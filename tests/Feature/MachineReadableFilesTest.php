<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MachineReadableFilesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_sitemap_index_points_to_segmented_sitemaps(): void
    {
        config(['app.url' => 'https://blog.example.test']);

        $response = $this->get('/sitemap.xml');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/xml');

        $xml = simplexml_load_string($response->getContent());

        $this->assertSame('sitemapindex', $xml->getName());
        $locations = array_map(
            fn (\SimpleXMLElement $location): string => (string) $location,
            $xml->xpath('//*[local-name()="sitemap"]/*[local-name()="loc"]'),
        );

        $this->assertSame([
            'https://blog.example.test/sitemap-posts.xml',
            'https://blog.example.test/sitemap-categories.xml',
            'https://blog.example.test/sitemap-tags.xml',
            'https://blog.example.test/sitemap-pages.xml',
        ], $locations);
    }

    public function test_robots_txt_references_sitemap_and_blocks_admin(): void
    {
        config(['app.url' => 'https://blog.example.test']);

        $this->assertFileDoesNotExist(public_path('robots.txt'));

        $response = $this->get('/robots.txt');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSee("User-agent: *\nDisallow: /admin", false)
            ->assertSee('Sitemap: https://blog.example.test/sitemap.xml')
            ->assertSee("User-agent: GPTBot\nDisallow: /admin\nAllow: /", false)
            ->assertSee("User-agent: OAI-SearchBot\nDisallow: /admin\nAllow: /", false)
            ->assertSee("User-agent: Google-Extended\nDisallow: /admin\nAllow: /", false)
            ->assertSee("User-agent: PerplexityBot\nDisallow: /admin\nAllow: /", false)
            ->assertSee("User-agent: ClaudeBot\nDisallow: /admin\nAllow: /", false);
    }

    public function test_sitemap_pages_includes_lastmod_when_there_are_no_published_posts(): void
    {
        config(['app.url' => 'https://blog.example.test']);

        $this->assertSame(0, Post::query()->published()->count());

        $response = $this->get('/sitemap-pages.xml');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/xml');

        $xml = simplexml_load_string($response->getContent());
        $urls = $xml->xpath('//*[local-name()="url"]');
        $lastmods = $xml->xpath('//*[local-name()="url"]/*[local-name()="lastmod"]');

        $this->assertNotEmpty($urls);
        $this->assertCount(count($urls), $lastmods);

        foreach ($lastmods as $lastmod) {
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', (string) $lastmod);
        }
    }

    public function test_segmented_sitemaps_and_feed_include_only_published_indexable_content(): void
    {
        config(['app.url' => 'https://blog.example.test']);
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Publica',
            'slug' => 'publica',
        ]);
        $emptyCategory = Category::create([
            'name' => 'Sem Publicos',
            'slug' => 'sem-publicos',
        ]);
        $tag = Tag::create([
            'name' => 'Indexavel',
            'slug' => 'indexavel',
        ]);
        $emptyTag = Tag::create([
            'name' => 'Privada',
            'slug' => 'privada',
        ]);

        $published = Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Post Indexavel',
            'slug' => 'post-indexavel',
            'excerpt' => 'Resumo publicado.',
            'content' => 'Conteudo publico.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $published->tags()->attach($tag);

        $noindex = Post::create([
            'user_id' => $author->id,
            'category_id' => $emptyCategory->id,
            'title' => 'Post Noindex',
            'slug' => 'post-noindex',
            'content' => 'Conteudo noindex.',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'meta_robots' => 'noindex,nofollow',
        ]);
        $noindex->tags()->attach($emptyTag);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Post Futuro',
            'slug' => 'post-futuro',
            'content' => 'Conteudo futuro.',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $this->get('/sitemap-posts.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml')
            ->assertSee('<loc>https://blog.example.test/posts/post-indexavel</loc>', false)
            ->assertDontSee('post-noindex')
            ->assertDontSee('post-futuro');

        $this->get('/sitemap-categories.xml')
            ->assertOk()
            ->assertSee('<loc>https://blog.example.test/categorias/publica</loc>', false)
            ->assertDontSee('sem-publicos');

        $this->get('/sitemap-tags.xml')
            ->assertOk()
            ->assertSee('<loc>https://blog.example.test/tags/indexavel</loc>', false)
            ->assertDontSee('privada');

        $this->get('/sitemap-pages.xml')
            ->assertOk()
            ->assertSee('<loc>https://blog.example.test/</loc>', false);

        $this->get('/feed.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/rss+xml')
            ->assertSee('<title>Post Indexavel</title>', false)
            ->assertDontSee('Post Noindex')
            ->assertDontSee('Post Futuro');
    }

    public function test_llms_txt_contains_site_summary_and_posts(): void
    {
        config([
            'app.name' => 'Blog MSP',
            'app.url' => 'https://blog.example.test',
        ]);
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Guias',
            'slug' => 'guias',
            'description' => 'Guias tecnicos para MSPs.',
        ]);
        $tag = Tag::create([
            'name' => 'Automacao',
            'slug' => 'automacao',
            'description' => 'Automacao de operacoes.',
        ]);

        $published = Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Runbooks para MSP',
            'slug' => 'runbooks-para-msp',
            'excerpt' => 'Como estruturar runbooks publicos.',
            'content' => 'Conteudo completo publicado.',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'ai_summary' => 'Resumo focado em agentes e mecanismos de resposta.',
        ]);
        $published->tags()->attach($tag);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Rascunho privado',
            'slug' => 'rascunho-privado',
            'content' => 'Conteudo privado.',
            'status' => 'draft',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/llms.txt');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSee('# Blog MSP')
            ->assertSee('Site: https://blog.example.test')
            ->assertSee('## Key Sections')
            ->assertSee('- Home: https://blog.example.test')
            ->assertSee('- Category: Guias - https://blog.example.test/categorias/guias')
            ->assertSee('- Tag: Automacao - https://blog.example.test/tags/automacao')
            ->assertSee('## Important Posts')
            ->assertSee('- Runbooks para MSP: https://blog.example.test/posts/runbooks-para-msp')
            ->assertSee('Resumo focado em agentes e mecanismos de resposta.')
            ->assertDontSee('Rascunho privado');
    }

    public function test_post_canonical_url_is_used_in_machine_readable_post_outputs(): void
    {
        config([
            'app.name' => 'Blog MSP',
            'app.url' => 'https://blog.example.test',
        ]);
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Canonical',
            'slug' => 'canonical',
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Canonical externo',
            'slug' => 'canonical-local-post',
            'excerpt' => 'Resumo canonical.',
            'content' => 'Conteudo canonical.',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'canonical_url' => 'https://canonical.example.com/original-post?ref=blog&src=msp',
            'ai_summary' => 'Resumo para llms canonical.',
        ]);

        $canonicalUrl = 'https://canonical.example.com/original-post?ref=blog&src=msp';
        $escapedCanonicalUrl = 'https://canonical.example.com/original-post?ref=blog&amp;src=msp';
        $localUrl = 'https://blog.example.test/posts/canonical-local-post';

        $this->get('/sitemap-posts.xml')
            ->assertOk()
            ->assertSee("<loc>{$escapedCanonicalUrl}</loc>", false)
            ->assertDontSee($localUrl);

        $this->get('/feed.xml')
            ->assertOk()
            ->assertSee("<link>{$escapedCanonicalUrl}</link>", false)
            ->assertSee("<guid isPermaLink=\"true\">{$escapedCanonicalUrl}</guid>", false)
            ->assertDontSee($localUrl);

        $this->get('/llms.txt')
            ->assertOk()
            ->assertSee("- Canonical externo: {$canonicalUrl}", false)
            ->assertDontSee($localUrl);
    }

    public function test_llms_txt_strips_html_from_text_fields(): void
    {
        config([
            'app.name' => '<strong>Blog</strong> MSP',
            'app.url' => 'https://blog.example.test',
        ]);
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Guias <em>MSP</em>',
            'slug' => 'guias-html',
        ]);
        $tag = Tag::create([
            'name' => 'Auto <script>alert("x")</script>macao',
            'slug' => 'tag-html',
        ]);

        $post = Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Runbooks <span>seguros</span>',
            'slug' => 'runbooks-html',
            'excerpt' => '<p>Resumo <strong>limpo</strong>.</p>',
            'content' => 'Conteudo publicado.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $post->tags()->attach($tag);

        $response = $this->get('/llms.txt');

        $response
            ->assertOk()
            ->assertSee('# Blog MSP')
            ->assertSee('- Category: Guias MSP - https://blog.example.test/categorias/guias-html')
            ->assertSee('- Tag: Auto alert("x")macao - https://blog.example.test/tags/tag-html', false)
            ->assertSee('- Runbooks seguros: https://blog.example.test/posts/runbooks-html')
            ->assertSee('Summary: Resumo limpo.')
            ->assertDontSee('<strong>', false)
            ->assertDontSee('<em>', false)
            ->assertDontSee('<script>', false)
            ->assertDontSee('<span>', false)
            ->assertDontSee('<p>', false);
    }

    public function test_xml_outputs_escape_special_characters_in_titles_and_urls(): void
    {
        config([
            'app.name' => 'Blog MSP & Partners',
            'app.url' => 'https://blog.example.test',
        ]);
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Ops & Infra',
            'slug' => 'ops&infra',
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'AT&T <Ops> "Guide"',
            'slug' => 'att-ops',
            'excerpt' => 'Resumo com R&D.',
            'content' => 'Conteudo publicado.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->get('/feed.xml')
            ->assertOk()
            ->assertSee('<title>Blog MSP &amp; Partners</title>', false)
            ->assertSee('<title>AT&amp;T &lt;Ops&gt; &quot;Guide&quot;</title>', false)
            ->assertSee('<description>Resumo com R&amp;D.</description>', false);

        $this->get('/sitemap-categories.xml')
            ->assertOk()
            ->assertSee('<loc>https://blog.example.test/categorias/ops&amp;infra</loc>', false);
    }
}
