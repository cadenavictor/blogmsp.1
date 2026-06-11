<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostMediaFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_post_without_date_defaults_to_now_and_appears(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Geral', 'slug' => 'geral']);

        $this->actingAs($admin)->post('/admin/posts', [
            'category_id' => $category->id,
            'title' => 'Sem data definida',
            'slug' => 'sem-data-definida',
            'content' => 'Conteudo do post publicado sem data.',
            'status' => 'published',
            'published_at' => '',
        ])->assertRedirect('/admin/posts');

        $post = Post::where('slug', 'sem-data-definida')->firstOrFail();

        $this->assertNotNull($post->published_at, 'published_at deveria ser preenchido com agora');
        $this->assertTrue($post->published_at->lessThanOrEqualTo(now()));

        // E aparece no blog (escopo published exige published_at <= now()).
        $this->assertTrue(Post::published()->whereKey($post->id)->exists());
    }

    public function test_cover_image_is_rendered_on_post_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Fotos', 'slug' => 'fotos']);
        Post::create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Post com capa',
            'slug' => 'post-com-capa',
            'content' => 'Conteudo.',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'cover_image_path' => 'uploads/2026/06/capa.jpg',
        ]);

        $this->get('/posts/post-com-capa')
            ->assertOk()
            ->assertSee('class="article-cover"', false)
            ->assertSee(asset('uploads/2026/06/capa.jpg'), false);
    }

    public function test_post_without_cover_has_no_cover_figure(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Sem foto', 'slug' => 'sem-foto']);
        Post::create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Post sem capa',
            'slug' => 'post-sem-capa',
            'content' => 'Conteudo.',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'cover_image_path' => null,
        ]);

        $this->get('/posts/post-sem-capa')
            ->assertOk()
            ->assertDontSee('class="article-cover"', false);
    }

    public function test_api_media_upload_stores_in_physical_uploads_dir(): void
    {
        Storage::fake('uploads');
        config(['services.codex.api_key' => 'secret-test-key']);

        $response = $this->post('/api/media', [
            'file' => UploadedFile::fake()->image('foto.jpg', 1000, 600),
        ], ['X-Api-Key' => 'secret-test-key', 'Accept' => 'application/json']);

        $response->assertCreated()->assertJsonStructure(['path', 'url']);

        $this->assertStringStartsWith('uploads/', $response->json('path'));
        $this->assertStringContainsString('/uploads/', $response->json('url'));
        $this->assertStringNotContainsString('/storage/', $response->json('url'));

        Storage::disk('uploads')->assertExists(str($response->json('path'))->after('uploads/')->toString());
    }

    public function test_api_media_upload_requires_key(): void
    {
        config(['services.codex.api_key' => 'secret-test-key']);

        $this->postJson('/api/media', [])->assertStatus(401);
    }
}
