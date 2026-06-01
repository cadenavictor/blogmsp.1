<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublicBlogTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_home_lists_published_posts_only(): void
    {
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Noticias',
            'slug' => 'noticias',
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Publicado na Home',
            'slug' => 'publicado-na-home',
            'excerpt' => 'Resumo publico.',
            'content' => 'Conteudo publico.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Rascunho Interno',
            'slug' => 'rascunho-interno',
            'content' => 'Conteudo privado.',
            'status' => 'draft',
            'published_at' => now()->subMinute(),
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Publicacao Futura',
            'slug' => 'publicacao-futura',
            'content' => 'Conteudo agendado.',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Publicado na Home')
            ->assertSee('Resumo publico.')
            ->assertDontSee('Rascunho Interno')
            ->assertDontSee('Publicacao Futura');
    }

    public function test_post_page_renders_content(): void
    {
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create(['name' => 'Ana Autora']);
        $category = Category::create([
            'name' => 'Guias',
            'slug' => 'guias',
        ]);

        $post = Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Guia Publico',
            'slug' => 'guia-publico',
            'excerpt' => 'Resumo do guia.',
            'content' => "Primeiro paragrafo.\n\nSegundo paragrafo.",
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $draft = Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Guia Rascunho',
            'slug' => 'guia-rascunho',
            'content' => 'Conteudo indisponivel.',
            'status' => 'draft',
            'published_at' => now()->subMinute(),
        ]);

        $future = Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Guia Futuro',
            'slug' => 'guia-futuro',
            'content' => 'Conteudo agendado.',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $this->get("/posts/{$post->slug}")
            ->assertOk()
            ->assertSee('Guia Publico')
            ->assertSee('Primeiro paragrafo.')
            ->assertSee('Segundo paragrafo.')
            ->assertSee('Guias')
            ->assertSee('Ana Autora')
            ->assertSee('Inicio');

        $this->get("/posts/{$draft->slug}")->assertNotFound();
        $this->get("/posts/{$future->slug}")->assertNotFound();
    }

    public function test_search_lists_published_posts_only_and_preserves_query_on_pagination(): void
    {
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Busca',
            'slug' => 'busca',
        ]);

        foreach (range(1, 11) as $number) {
            Post::create([
                'user_id' => $author->id,
                'category_id' => $category->id,
                'title' => "Indice Publico {$number}",
                'slug' => "indice-publico-{$number}",
                'excerpt' => "Resultado publicado {$number}.",
                'content' => 'Conteudo pesquisavel.',
                'status' => 'published',
                'published_at' => now()->subMinutes($number),
            ]);
        }

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Indice Rascunho',
            'slug' => 'indice-rascunho',
            'content' => 'Conteudo privado pesquisavel.',
            'status' => 'draft',
            'published_at' => now()->subMinute(),
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Indice Futuro',
            'slug' => 'indice-futuro',
            'content' => 'Conteudo agendado pesquisavel.',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $this->get('/buscar?q=Indice')
            ->assertOk()
            ->assertSee('Indice Publico 1')
            ->assertDontSee('Indice Rascunho')
            ->assertDontSee('Indice Futuro')
            ->assertSee('q=Indice&amp;page=2', false);
    }

    public function test_category_lists_published_posts_only(): void
    {
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Categoria Publica',
            'slug' => 'categoria-publica',
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Post Publicado na Categoria',
            'slug' => 'post-publicado-na-categoria',
            'content' => 'Conteudo publico.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Rascunho na Categoria',
            'slug' => 'rascunho-na-categoria',
            'content' => 'Conteudo privado.',
            'status' => 'draft',
            'published_at' => now()->subMinute(),
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Futuro na Categoria',
            'slug' => 'futuro-na-categoria',
            'content' => 'Conteudo agendado.',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $this->get("/categorias/{$category->slug}")
            ->assertOk()
            ->assertSee('Post Publicado na Categoria')
            ->assertDontSee('Rascunho na Categoria')
            ->assertDontSee('Futuro na Categoria');
    }

    public function test_tag_lists_published_posts_only(): void
    {
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Tags',
            'slug' => 'tags',
        ]);
        $tag = Tag::create([
            'name' => 'Publica',
            'slug' => 'publica',
        ]);

        $published = Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Post Publicado com Tag',
            'slug' => 'post-publicado-com-tag',
            'content' => 'Conteudo publico.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $draft = Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Rascunho com Tag',
            'slug' => 'rascunho-com-tag',
            'content' => 'Conteudo privado.',
            'status' => 'draft',
            'published_at' => now()->subMinute(),
        ]);

        $future = Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Futuro com Tag',
            'slug' => 'futuro-com-tag',
            'content' => 'Conteudo agendado.',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $tag->posts()->attach([$published->id, $draft->id, $future->id]);

        $this->get("/tags/{$tag->slug}")
            ->assertOk()
            ->assertSee('Post Publicado com Tag')
            ->assertDontSee('Rascunho com Tag')
            ->assertDontSee('Futuro com Tag');
    }

    public function test_author_lists_published_posts_only(): void
    {
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create(['name' => 'Autor Publico']);
        $category = Category::create([
            'name' => 'Autores',
            'slug' => 'autores',
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Post Publicado do Autor',
            'slug' => 'post-publicado-do-autor',
            'content' => 'Conteudo publico.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Rascunho do Autor',
            'slug' => 'rascunho-do-autor',
            'content' => 'Conteudo privado.',
            'status' => 'draft',
            'published_at' => now()->subMinute(),
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Futuro do Autor',
            'slug' => 'futuro-do-autor',
            'content' => 'Conteudo agendado.',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $this->get("/autores/{$author->id}")
            ->assertOk()
            ->assertSee('Post Publicado do Autor')
            ->assertDontSee('Rascunho do Autor')
            ->assertDontSee('Futuro do Autor');
    }

    public function test_author_without_published_posts_returns_not_found(): void
    {
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Sem Publicados',
            'slug' => 'sem-publicados',
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Rascunho do Perfil',
            'slug' => 'rascunho-do-perfil',
            'content' => 'Conteudo privado.',
            'status' => 'draft',
            'published_at' => now()->subMinute(),
        ]);

        $this->get("/autores/{$author->id}")->assertNotFound();
    }
}
