<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\NewsMonitor;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiContentTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, string> */
    private array $headers = ['X-Api-Key' => 'secret-test-key'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.codex.api_key' => 'secret-test-key']);
    }

    private function fakeFeed(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"><channel><title>Google News</title>
<item>
  <title>Noticia de teste - Fonte Y</title>
  <link>https://news.google.com/articles/zzz</link>
  <pubDate>Mon, 01 Jun 2026 10:00:00 GMT</pubDate>
  <description>resumo</description>
  <source url="https://fontey.com">Fonte Y</source>
</item>
</channel></rss>
XML;

        Http::fake(['news.google.com/*' => Http::response($xml, 200)]);
    }

    public function test_news_endpoint_returns_normalized_json(): void
    {
        $this->fakeFeed();

        $this->getJson('/api/news?q=teste', $this->headers)
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('data.0.title', 'Noticia de teste')
            ->assertJsonPath('data.0.source', 'Fonte Y');
    }

    public function test_news_endpoint_requires_query_or_monitor(): void
    {
        $this->getJson('/api/news', $this->headers)->assertStatus(422);
    }

    public function test_news_endpoint_works_with_saved_monitor(): void
    {
        $this->fakeFeed();
        NewsMonitor::create([
            'name' => 'Tema',
            'slug' => 'tema',
            'keywords' => 'tema teste',
            'language' => 'pt-BR',
            'country' => 'BR',
            'max_results' => 10,
            'is_active' => true,
        ]);

        $this->getJson('/api/news?monitor=tema', $this->headers)
            ->assertOk()
            ->assertJsonPath('monitor', 'tema')
            ->assertJsonPath('count', 1);
    }

    public function test_digest_returns_blog_themes_with_news(): void
    {
        $this->fakeFeed();
        NewsMonitor::create([
            'name' => 'Tecnologia',
            'slug' => 'tecnologia',
            'keywords' => 'tecnologia ia',
            'language' => 'pt-BR',
            'country' => 'BR',
            'max_results' => 10,
            'is_active' => true,
        ]);
        NewsMonitor::create([
            'name' => 'Inativo',
            'slug' => 'inativo',
            'keywords' => 'nao deve aparecer',
            'language' => 'pt-BR',
            'country' => 'BR',
            'max_results' => 10,
            'is_active' => false,
        ]);

        $this->getJson('/api/news/digest?limit=3', $this->headers)
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('data.0.monitor.slug', 'tecnologia')
            ->assertJsonPath('data.0.items.0.source', 'Fonte Y');
    }

    public function test_create_post_stores_and_optimizes(): void
    {
        Http::fake(['api.indexnow.org/*' => Http::response('', 200)]);
        User::factory()->create(['is_admin' => true]);

        $response = $this->postJson('/api/posts', [
            'title' => 'Como a IA muda o jornalismo',
            'content' => '<p>Primeiro paragrafo com bastante conteudo relevante para o leitor.</p><h2>Secao</h2><p>Mais texto aqui.</p>',
            'category' => 'Tecnologia',
            'tags' => ['IA', 'Jornalismo'],
        ], $this->headers);

        $response->assertCreated()
            ->assertJsonPath('data.slug', 'como-a-ia-muda-o-jornalismo')
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.category.name', 'Tecnologia');

        $post = Post::firstOrFail();

        $this->assertNotEmpty($post->excerpt, 'excerpt should be auto-derived');
        $this->assertNotEmpty($post->seo_title, 'seo_title should be auto-derived');
        $this->assertNotEmpty($post->seo_description, 'seo_description should be auto-derived');
        $this->assertNotNull($post->published_at);
        $this->assertGreaterThanOrEqual(1, $post->reading_time);
        $this->assertEqualsCanonicalizing(['IA', 'Jornalismo'], $post->tags()->pluck('name')->all());
        $this->assertDatabaseHas('categories', ['name' => 'Tecnologia']);
    }

    public function test_create_post_generates_unique_slug(): void
    {
        User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Geral', 'slug' => 'geral']);
        Post::create([
            'user_id' => User::first()->id,
            'category_id' => $category->id,
            'title' => 'Titulo repetido',
            'slug' => 'titulo-repetido',
            'content' => 'x',
            'status' => 'draft',
        ]);

        $this->postJson('/api/posts', [
            'title' => 'Titulo repetido',
            'content' => '<p>Outro conteudo.</p>',
            'status' => 'draft',
        ], $this->headers)->assertCreated();

        $this->assertDatabaseHas('posts', ['slug' => 'titulo-repetido-2']);
    }

    public function test_create_post_validates_required_fields(): void
    {
        $this->postJson('/api/posts', ['content' => '<p>sem titulo</p>'], $this->headers)
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_sanitizes_script_tags_in_content(): void
    {
        User::factory()->create(['is_admin' => true]);

        $this->postJson('/api/posts', [
            'title' => 'Post seguro',
            'content' => '<p>ok</p><script>alert(1)</script>',
            'status' => 'draft',
        ], $this->headers)->assertCreated();

        $post = Post::firstOrFail();
        $this->assertStringNotContainsString('<script', $post->content);
    }

    public function test_taxonomy_endpoints(): void
    {
        Category::create(['name' => 'Tech', 'slug' => 'tech']);

        $this->getJson('/api/categories', $this->headers)
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'tech');

        $this->getJson('/api/tags', $this->headers)->assertOk();
    }
}
