<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_dashboard_with_recent_published_post_and_machine_file_links(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create([
            'name' => 'Noticias',
            'slug' => 'noticias',
        ]);

        Post::create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Post publicado no painel',
            'slug' => 'post-publicado-no-painel',
            'content' => 'Conteudo do post publicado.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Post publicado no painel')
            ->assertSee('/sitemap.xml')
            ->assertSee('/robots.txt')
            ->assertSee('/llms.txt');
    }
}
