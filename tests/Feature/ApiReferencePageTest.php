<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiReferencePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_integrations_page_documents_daily_codex_publishing_routine(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/integracoes')
            ->assertOk()
            ->assertSee('Rotina diaria de posts automaticos')
            ->assertSee('status: published')
            ->assertSee('node codex/blog.mjs research --limit 6')
            ->assertSee('node codex/blog.mjs publish artigo.json')
            ->assertSee('codex/prompts/daily-news-post.md')
            ->assertSee('BLOG_API_URL')
            ->assertSee('BLOG_API_KEY');
    }
}
