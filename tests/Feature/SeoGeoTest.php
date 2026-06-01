<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SeoGeoTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function publishPost(array $attributes = []): Post
    {
        $author = User::factory()->create(['name' => 'Ana Editora']);
        $category = Category::create(['name' => 'Tecnologia', 'slug' => 'tecnologia']);

        return Post::create(array_merge([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Post GEO',
            'slug' => 'post-geo',
            'excerpt' => 'Resumo do post.',
            'content' => '<h2>Subtitulo</h2><p>Conteudo rico.</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ], $attributes));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function jsonLdFromHtml(string $html): array
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

        return collect($matches[1])
            ->map(fn (string $json): array => json_decode($json, true, flags: JSON_THROW_ON_ERROR))
            ->all();
    }

    public function test_home_emits_organization_and_website_json_ld(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);
        SiteSetting::create([
            'site_name' => 'Blog Tech',
            'organization_name' => 'Tech Corp',
            'social_links' => ['x' => 'https://x.com/techcorp'],
        ]);

        $jsonLd = $this->jsonLdFromHtml($this->get('/')->assertOk()->getContent());

        $website = collect($jsonLd)->firstWhere('@type', 'WebSite');
        $organization = collect($jsonLd)->firstWhere('@type', 'Organization');

        $this->assertNotNull($website, 'WebSite JSON-LD ausente');
        $this->assertNotNull($organization, 'Organization JSON-LD ausente');
        $this->assertSame('Tech Corp', $organization['name']);
        $this->assertContains('https://x.com/techcorp', $organization['sameAs']);
        $this->assertSame('SearchAction', $website['potentialAction']['@type']);
    }

    public function test_post_blogposting_includes_publisher(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);
        SiteSetting::create(['organization_name' => 'Editora XPTO']);

        $this->publishPost();

        $jsonLd = $this->jsonLdFromHtml($this->get('/posts/post-geo')->assertOk()->getContent());
        $blogPosting = collect($jsonLd)->firstWhere('@type', 'BlogPosting');

        $this->assertSame('Editora XPTO', $blogPosting['publisher']['name']);
    }

    public function test_post_renders_faq_schema_when_present(): void
    {
        $this->publishPost([
            'slug' => 'post-faq',
            'faq_items' => [
                ['question' => 'O que e GEO?', 'answer' => 'Otimizacao para motores generativos.'],
            ],
        ]);

        $jsonLd = $this->jsonLdFromHtml($this->get('/posts/post-faq')->assertOk()->getContent());
        $faq = collect($jsonLd)->firstWhere('@type', 'FAQPage');

        $this->assertNotNull($faq, 'FAQPage JSON-LD ausente');
        $this->assertSame('O que e GEO?', $faq['mainEntity'][0]['name']);
    }

    public function test_post_without_faq_has_no_faq_schema(): void
    {
        $this->publishPost(['slug' => 'post-sem-faq']);

        $jsonLd = $this->jsonLdFromHtml($this->get('/posts/post-sem-faq')->assertOk()->getContent());

        $this->assertNull(collect($jsonLd)->firstWhere('@type', 'FAQPage'));
    }

    public function test_post_content_is_rendered_as_html(): void
    {
        $this->publishPost(['slug' => 'post-html']);

        $this->get('/posts/post-html')
            ->assertOk()
            ->assertSee('<h2>Subtitulo</h2>', false)
            ->assertSee('<p>Conteudo rico.</p>', false);
    }

    public function test_default_og_image_is_used_when_post_has_no_cover(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);
        SiteSetting::create(['default_og_image' => 'https://cdn.example.com/default-og.jpg']);

        $this->publishPost(['slug' => 'post-sem-capa', 'cover_image_path' => null]);

        $this->get('/posts/post-sem-capa')
            ->assertOk()
            ->assertSee('<meta property="og:image" content="https://cdn.example.com/default-og.jpg">', false);
    }

    public function test_llms_txt_uses_configured_summary(): void
    {
        config(['app.url' => 'https://blog.example.test']);
        SiteSetting::create(['llms_summary' => 'Resumo customizado para IAs.']);

        $this->get('/llms.txt')
            ->assertOk()
            ->assertSee('Summary: Resumo customizado para IAs.');
    }

    public function test_robots_blocks_training_bots_when_disabled(): void
    {
        config(['app.url' => 'https://blog.example.test']);
        SiteSetting::create([
            'allow_ai_training' => false,
            'allow_ai_search' => true,
        ]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee("User-agent: GPTBot\nDisallow: /\n", false)
            ->assertSee("User-agent: OAI-SearchBot\nDisallow: /admin\nAllow: /", false);
    }
}
