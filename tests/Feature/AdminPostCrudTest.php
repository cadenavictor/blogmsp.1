<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPostCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_posts_index(): void
    {
        $this->get('/admin/posts')->assertRedirect('/login');
    }

    public function test_authenticated_non_admin_receives_forbidden_from_admin_posts_index(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/posts')
            ->assertForbidden();
    }

    public function test_admin_can_create_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create([
            'name' => 'Noticias',
            'slug' => 'noticias',
        ]);
        $tags = collect([
            Tag::create(['name' => 'Laravel', 'slug' => 'laravel']),
            Tag::create(['name' => 'SEO', 'slug' => 'seo']),
        ]);

        $response = $this->actingAs($admin)->post('/admin/posts', [
            'category_id' => $category->id,
            'title' => 'Novo post editorial',
            'slug' => 'novo-post-editorial',
            'excerpt' => 'Resumo do post.',
            'content' => 'Conteudo principal do post.',
            'status' => 'published',
            'published_at' => '2026-05-31 14:30:00',
            'seo_title' => 'Titulo SEO',
            'seo_description' => 'Descricao para motores de busca.',
            'tag_ids' => $tags->pluck('id')->all(),
        ]);

        $response->assertRedirect('/admin/posts');
        $response->assertSessionHasNoErrors();

        $post = Post::where('slug', 'novo-post-editorial')->firstOrFail();

        $this->assertSame($admin->id, $post->user_id);
        $this->assertSame($category->id, $post->category_id);
        $this->assertSame('Novo post editorial', $post->title);
        $this->assertSame('published', $post->status);
        $this->assertSame('Titulo SEO', $post->seo_title);
        $this->assertEqualsCanonicalizing(
            $tags->pluck('id')->all(),
            $post->tags()->pluck('tags.id')->all(),
        );
    }

    public function test_create_rejects_invalid_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory();

        $response = $this->actingAs($admin)->from('/admin/posts/create')->post('/admin/posts', [
            ...$this->validPostPayload($category),
            'status' => 'archived',
        ]);

        $response
            ->assertRedirect('/admin/posts/create')
            ->assertSessionHasErrors('status');

        $this->assertDatabaseMissing('posts', [
            'slug' => 'valid-post',
        ]);
    }

    public function test_create_rejects_invalid_json_metadata_structure(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory();

        $response = $this->actingAs($admin)->from('/admin/posts/create')->post('/admin/posts', [
            ...$this->validPostPayload($category),
            'key_takeaways' => ['', 'Readable item'],
            'entities' => ['Laravel', ''],
            'faq_items' => [
                ['question' => 'What is this?', 'answer' => 'A valid answer.'],
                ['question' => '', 'answer' => 'Missing question.'],
                ['question' => 'Missing answer.'],
            ],
        ]);

        $response
            ->assertRedirect('/admin/posts/create')
            ->assertSessionHasErrors([
                'key_takeaways.0',
                'entities.1',
                'faq_items.1.question',
                'faq_items.2.answer',
            ]);

        $this->assertDatabaseMissing('posts', [
            'slug' => 'valid-post',
        ]);
    }

    public function test_create_rejects_invalid_tag_ids(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory();

        $response = $this->actingAs($admin)->from('/admin/posts/create')->post('/admin/posts', [
            ...$this->validPostPayload($category),
            'tag_ids' => [999],
        ]);

        $response
            ->assertRedirect('/admin/posts/create')
            ->assertSessionHasErrors('tag_ids.0');

        $this->assertDatabaseMissing('posts', [
            'slug' => 'valid-post',
        ]);
    }

    public function test_mass_assignment_user_id_does_not_change_author_on_create(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherUser = User::factory()->create();
        $category = $this->createCategory();

        $this->actingAs($admin)->post('/admin/posts', [
            ...$this->validPostPayload($category),
            'user_id' => $otherUser->id,
        ])->assertRedirect('/admin/posts');

        $post = Post::where('slug', 'valid-post')->firstOrFail();

        $this->assertSame($admin->id, $post->user_id);
    }

    public function test_admin_post_index_can_search(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create([
            'name' => 'Analises',
            'slug' => 'analises',
        ]);

        Post::create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Guia de busca editorial',
            'slug' => 'guia-de-busca-editorial',
            'content' => 'Conteudo sobre encontrar posts.',
            'status' => 'draft',
        ]);

        Post::create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Outro assunto',
            'slug' => 'outro-assunto',
            'content' => 'Conteudo sem o termo pesquisado.',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->get('/admin/posts?q=busca')
            ->assertOk()
            ->assertSee('Guia de busca editorial')
            ->assertDontSee('Outro assunto');
    }

    public function test_admin_post_index_combines_search_and_status_filters(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory();

        Post::create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Relatorio de seguranca',
            'slug' => 'relatorio-de-seguranca',
            'content' => 'Conteudo pesquisavel.',
            'status' => 'published',
        ]);

        Post::create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Relatorio de seguranca rascunho',
            'slug' => 'relatorio-de-seguranca-rascunho',
            'content' => 'Conteudo pesquisavel.',
            'status' => 'draft',
        ]);

        Post::create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Publicado sem termo',
            'slug' => 'publicado-sem-termo',
            'content' => 'Outro conteudo.',
            'status' => 'published',
        ]);

        $this->actingAs($admin)
            ->get('/admin/posts?q=seguranca&status=published')
            ->assertOk()
            ->assertSee('Relatorio de seguranca')
            ->assertDontSee('Relatorio de seguranca rascunho')
            ->assertDontSee('Publicado sem termo');
    }

    public function test_admin_can_update_post_with_empty_tags(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create([
            'name' => 'Guias',
            'slug' => 'guias',
        ]);
        $tag = Tag::create(['name' => 'PHP', 'slug' => 'php']);
        $post = Post::create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Post com tag',
            'slug' => 'post-com-tag',
            'content' => 'Conteudo existente.',
            'status' => 'draft',
        ]);
        $post->tags()->attach($tag->id);

        $this->actingAs($admin)->from("/admin/posts/{$post->id}/edit")->put("/admin/posts/{$post->id}", [
            'category_id' => $category->id,
            'title' => 'Post sem tags',
            'slug' => 'post-sem-tags',
            'content' => 'Conteudo atualizado.',
            'status' => 'draft',
            'tag_ids' => [''],
        ]);

        $this->assertDatabaseMissing('post_tag', [
            'post_id' => $post->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_update_allows_post_to_keep_its_current_slug(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory();
        $post = $this->createPost($admin, $category, [
            'title' => 'Original title',
            'slug' => 'original-slug',
        ]);

        $response = $this->actingAs($admin)->from("/admin/posts/{$post->id}/edit")->put("/admin/posts/{$post->id}", [
            ...$this->validPostPayload($category),
            'title' => 'Updated title',
            'slug' => 'original-slug',
        ]);

        $response
            ->assertRedirect("/admin/posts/{$post->id}/edit")
            ->assertSessionHasNoErrors();

        $this->assertSame('Updated title', $post->refresh()->title);
        $this->assertSame('original-slug', $post->slug);
    }

    public function test_update_rejects_slug_used_by_another_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory();
        $post = $this->createPost($admin, $category, [
            'slug' => 'current-slug',
        ]);
        $this->createPost($admin, $category, [
            'title' => 'Existing post',
            'slug' => 'existing-slug',
        ]);

        $response = $this->actingAs($admin)->from("/admin/posts/{$post->id}/edit")->put("/admin/posts/{$post->id}", [
            ...$this->validPostPayload($category),
            'slug' => 'existing-slug',
        ]);

        $response
            ->assertRedirect("/admin/posts/{$post->id}/edit")
            ->assertSessionHasErrors('slug');

        $this->assertSame('current-slug', $post->refresh()->slug);
    }

    public function test_edit_form_preserves_unchecked_featured_and_selected_tags_after_validation_error(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory();
        $tag = Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $post = $this->createPost($admin, $category, [
            'featured' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->followingRedirects()
            ->from("/admin/posts/{$post->id}/edit")
            ->put("/admin/posts/{$post->id}", [
                ...$this->validPostPayload($category),
                'slug' => 'invalid slug',
                'featured' => '0',
                'tag_ids' => ['', (string) $tag->id],
            ]);

        $html = $response
            ->assertOk()
            ->assertSee('Revise os campos destacados.')
            ->content();

        $this->assertStringContainsString('<input type="hidden" name="featured" value="0">', $html);
        $this->assertMatchesRegularExpression(
            '/<input type="checkbox" name="featured" value="1"(?![^>]*checked)[^>]*>/',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/<input type="checkbox" name="tag_ids\[\]" value="'.preg_quote((string) $tag->id, '/').'"[^>]*checked[^>]*>/',
            $html,
        );
    }

    public function test_mass_assignment_user_id_does_not_change_author_on_update(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherUser = User::factory()->create();
        $category = $this->createCategory();
        $post = $this->createPost($admin, $category);

        $this->actingAs($admin)->put("/admin/posts/{$post->id}", [
            ...$this->validPostPayload($category),
            'user_id' => $otherUser->id,
        ])->assertRedirect("/admin/posts/{$post->id}/edit");

        $this->assertSame($admin->id, $post->refresh()->user_id);
    }

    public function test_admin_can_destroy_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory();
        $post = $this->createPost($admin, $category);

        $this->actingAs($admin)
            ->delete("/admin/posts/{$post->id}")
            ->assertRedirect('/admin/posts');

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPostPayload(Category $category): array
    {
        return [
            'category_id' => $category->id,
            'title' => 'Valid post',
            'slug' => 'valid-post',
            'excerpt' => 'Valid excerpt.',
            'content' => 'Valid content.',
            'status' => 'draft',
        ];
    }

    private function createCategory(array $attributes = []): Category
    {
        return Category::create($attributes + [
            'name' => 'Default category',
            'slug' => 'default-category',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createPost(User $author, Category $category, array $attributes = []): Post
    {
        return Post::create($attributes + [
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Existing post',
            'slug' => 'existing-post',
            'content' => 'Existing content.',
            'status' => 'draft',
        ]);
    }
}
