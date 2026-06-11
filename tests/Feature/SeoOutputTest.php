<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SeoOutputTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_post_outputs_meta_tags_and_json_ld(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create([
            'name' => 'Ana Editora',
        ]);
        $category = Category::create([
            'name' => 'Guias SEO',
            'slug' => 'guias-seo',
        ]);
        $post = Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Titulo Visivel',
            'slug' => 'titulo-visivel',
            'excerpt' => 'Resumo editorial curto.',
            'content' => 'Conteudo completo do post.',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'seo_title' => 'Titulo SEO completo',
            'seo_description' => 'Descricao SEO que aparece nos buscadores.',
            'ai_summary' => 'Resumo gerado por IA para leitura rapida.',
            'entities' => ['Laravel', 'SEO tecnico'],
        ]);

        $response = $this->get('/posts/titulo-visivel');

        $response
            ->assertOk()
            ->assertSee('<title>Titulo SEO completo</title>', false)
            ->assertSee('<meta name="description" content="Descricao SEO que aparece nos buscadores.">', false)
            ->assertSee('<meta property="og:title" content="Titulo SEO completo">', false)
            // ai_summary nao e mais exibido visivelmente no post (segue em llms.txt/meta).
            ->assertDontSee('Resumo gerado por IA para leitura rapida.');

        $jsonLd = $this->jsonLdFromHtml($response->getContent());

        $blogPosting = $this->jsonLdOfType($jsonLd, 'BlogPosting');
        $this->assertSame('Titulo SEO completo', $blogPosting['headline']);
        $this->assertSame('Descricao SEO que aparece nos buscadores.', $blogPosting['description']);
        $this->assertSame($post->published_at->toAtomString(), $blogPosting['datePublished']);
        $this->assertSame($post->updated_at->toAtomString(), $blogPosting['dateModified']);
        $this->assertSame('Ana Editora', $blogPosting['author']['name']);
        $this->assertSame('http://127.0.0.1:8000/posts/titulo-visivel', $blogPosting['mainEntityOfPage']);
        $this->assertSame('Guias SEO', $blogPosting['articleSection']);
        $this->assertSame('Guias SEO', $blogPosting['category']);
        $this->assertSame(['Laravel', 'SEO tecnico'], $blogPosting['keywords']);

        $breadcrumb = $this->jsonLdOfType($jsonLd, 'BreadcrumbList');
        $this->assertSame('Inicio', $breadcrumb['itemListElement'][0]['name']);
        $this->assertSame('http://127.0.0.1:8000', $breadcrumb['itemListElement'][0]['item']);
        $this->assertSame('Titulo Visivel', $breadcrumb['itemListElement'][1]['name']);
        $this->assertSame('http://127.0.0.1:8000/posts/titulo-visivel', $breadcrumb['itemListElement'][1]['item']);
    }

    public function test_post_escapes_json_ld_script_breakout_payloads(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);
        Carbon::setTestNow('2026-05-31 12:00:00');

        $payload = '</script><script>alert(1)</script>';
        $author = User::factory()->create([
            'name' => "Autor {$payload}",
        ]);
        $category = Category::create([
            'name' => "Categoria {$payload}",
            'slug' => 'categoria-xss',
        ]);
        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => "Titulo {$payload}",
            'slug' => 'titulo-xss',
            'excerpt' => 'Resumo editorial curto.',
            'content' => 'Conteudo completo do post.',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'seo_description' => "Descricao {$payload}",
            'ai_summary' => "Resumo IA {$payload}",
            'entities' => [
                "Entidade {$payload}",
                ['name' => "Outra {$payload}"],
            ],
        ]);

        $response = $this->get('/posts/titulo-xss');
        $html = $response->getContent();
        $escapedPayload = '\\u003C/script\\u003E\\u003Cscript\\u003Ealert(1)\\u003C/script\\u003E';

        $response->assertOk();

        $this->assertStringNotContainsString($payload, $html);
        $this->assertStringContainsString($escapedPayload, $html);

        $blogPosting = $this->jsonLdOfType($this->jsonLdFromHtml($html), 'BlogPosting');

        $this->assertSame("Titulo {$payload}", $blogPosting['headline']);
        $this->assertSame("Descricao {$payload}", $blogPosting['description']);
        $this->assertSame("Autor {$payload}", $blogPosting['author']['name']);
        $this->assertSame("Categoria {$payload}", $blogPosting['category']);
        $this->assertSame(["Entidade {$payload}", "Outra {$payload}"], $blogPosting['keywords']);
    }

    public function test_protocol_relative_cover_image_is_rendered_as_https_url(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Imagens',
            'slug' => 'imagens',
        ]);
        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Post com imagem',
            'slug' => 'post-com-imagem',
            'excerpt' => 'Resumo com imagem.',
            'content' => 'Conteudo completo do post.',
            'cover_image_path' => '//cdn.example.com/imagem.jpg',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/posts/post-com-imagem');
        $html = $response->getContent();

        $response
            ->assertOk()
            ->assertSee('<meta property="og:image" content="https://cdn.example.com/imagem.jpg">', false)
            ->assertSee('<meta name="twitter:image" content="https://cdn.example.com/imagem.jpg">', false);

        $this->assertStringNotContainsString('content="//cdn.example.com/imagem.jpg"', $html);
        $this->assertSame(
            'https://cdn.example.com/imagem.jpg',
            $this->jsonLdOfType($this->jsonLdFromHtml($html), 'BlogPosting')['image'],
        );
    }

    public function test_public_upload_cover_image_is_rendered_without_storage_link_url(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Imagens',
            'slug' => 'imagens',
        ]);
        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Post com upload publico',
            'slug' => 'post-com-upload-publico',
            'excerpt' => 'Resumo com imagem publica.',
            'content' => 'Conteudo completo do post.',
            'cover_image_path' => 'uploads/2026/06/capa.jpg',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/posts/post-com-upload-publico');
        $html = $response->getContent();

        $response
            ->assertOk()
            ->assertSee('<meta property="og:image" content="http://127.0.0.1:8000/uploads/2026/06/capa.jpg">', false)
            ->assertDontSee('/storage/uploads/2026/06/capa.jpg', false);

        $this->assertSame(
            'http://127.0.0.1:8000/uploads/2026/06/capa.jpg',
            $this->jsonLdOfType($this->jsonLdFromHtml($html), 'BlogPosting')['image'],
        );
    }

    public function test_null_optional_seo_fields_do_not_break_json_ld(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create([
            'name' => 'Ana Editora',
        ]);
        $category = Category::create([
            'name' => 'Sem opcionais',
            'slug' => 'sem-opcionais',
        ]);
        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Titulo sem opcionais',
            'slug' => 'titulo-sem-opcionais',
            'excerpt' => null,
            'content' => 'Conteudo usado como descricao quando campos SEO estao nulos.',
            'cover_image_path' => null,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'seo_title' => null,
            'seo_description' => null,
            'canonical_url' => null,
            'meta_robots' => null,
            'og_title' => null,
            'og_description' => null,
            'entities' => null,
        ]);

        $response = $this->get('/posts/titulo-sem-opcionais');
        $blogPosting = $this->jsonLdOfType($this->jsonLdFromHtml($response->getContent()), 'BlogPosting');

        $response->assertOk();
        $this->assertArrayNotHasKey('image', $blogPosting);
        $this->assertArrayNotHasKey('keywords', $blogPosting);
        $this->assertNotNull($blogPosting['description']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function jsonLdFromHtml(string $html): array
    {
        preg_match_all(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $html,
            $matches,
        );

        return collect($matches[1])
            ->map(fn (string $json): array => json_decode($json, true, flags: JSON_THROW_ON_ERROR))
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $jsonLd
     * @return array<string, mixed>
     */
    private function jsonLdOfType(array $jsonLd, string $type): array
    {
        $match = collect($jsonLd)->firstWhere('@type', $type);

        $this->assertIsArray($match, "JSON-LD {$type} was not rendered.");

        return $match;
    }
}
