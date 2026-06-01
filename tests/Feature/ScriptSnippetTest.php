<?php

namespace Tests\Feature;

use App\Models\ScriptSnippet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScriptSnippetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_scripts_index(): void
    {
        $this->get('/admin/scripts')->assertRedirect('/login');
    }

    public function test_authenticated_non_admin_receives_forbidden_from_admin_scripts_index(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/scripts')
            ->assertForbidden();
    }

    public function test_active_head_script_is_rendered_and_inactive_script_is_hidden(): void
    {
        $activeContent = '<meta name="google-site-verification" content="active-token">';
        $inactiveContent = '<script>window.inactiveSnippet = true;</script>';

        DB::table('script_snippets')->insert([
            [
                'name' => 'Search Console',
                'provider' => 'google_search_console',
                'position' => 'head_end',
                'content' => $activeContent,
                'is_active' => true,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Disabled script',
                'provider' => 'custom',
                'position' => 'head_end',
                'content' => $inactiveContent,
                'is_active' => false,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee($activeContent, false)
            ->assertDontSee($inactiveContent, false);
    }

    public function test_active_scripts_are_rendered_in_each_layout_position(): void
    {
        $snippets = [
            'head_start' => '<meta name="head-start-snippet" content="1">',
            'head_end' => '<meta name="head-end-snippet" content="1">',
            'body_start' => '<script>window.bodyStartSnippet = true;</script>',
            'body_end' => '<script>window.bodyEndSnippet = true;</script>',
        ];

        foreach ($snippets as $position => $content) {
            ScriptSnippet::create([
                'name' => "Snippet {$position}",
                'provider' => 'custom',
                'position' => $position,
                'content' => $content,
                'is_active' => true,
            ]);
        }

        $response = $this->get('/')->assertOk();

        foreach ($snippets as $content) {
            $response->assertSee($content, false);
        }
    }

    public function test_public_home_page_does_not_break_when_script_snippets_table_is_missing(): void
    {
        Schema::dropIfExists('script_snippets');

        $this->get('/')
            ->assertOk()
            ->assertSee('Blog MSP');
    }

    public function test_admin_can_create_update_and_delete_script_snippet(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $createResponse = $this->actingAs($admin)->post('/admin/scripts', [
            'name' => 'Search Console',
            'provider' => 'google_search_console',
            'position' => 'head_end',
            'content' => '<meta name="google-site-verification" content="created-token">',
            'is_active' => '1',
            'notes' => 'Created note',
        ]);

        $createResponse
            ->assertRedirect('/admin/scripts')
            ->assertSessionHasNoErrors();

        $snippet = ScriptSnippet::where('name', 'Search Console')->firstOrFail();

        $this->assertSame('google_search_console', $snippet->provider);
        $this->assertSame('head_end', $snippet->position);
        $this->assertTrue($snippet->is_active);

        $updateResponse = $this->actingAs($admin)->from("/admin/scripts/{$snippet->id}/edit")->put("/admin/scripts/{$snippet->id}", [
            'name' => 'Updated Search Console',
            'provider' => 'custom',
            'position' => 'body_end',
            'content' => '<script>window.updatedSnippet = true;</script>',
            'is_active' => '0',
            'notes' => 'Updated note',
        ]);

        $updateResponse
            ->assertRedirect("/admin/scripts/{$snippet->id}/edit")
            ->assertSessionHasNoErrors();

        $snippet->refresh();

        $this->assertSame('Updated Search Console', $snippet->name);
        $this->assertSame('custom', $snippet->provider);
        $this->assertSame('body_end', $snippet->position);
        $this->assertSame('<script>window.updatedSnippet = true;</script>', $snippet->content);
        $this->assertFalse($snippet->is_active);
        $this->assertSame('Updated note', $snippet->notes);

        $this->actingAs($admin)
            ->delete("/admin/scripts/{$snippet->id}")
            ->assertRedirect('/admin/scripts')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('script_snippets', [
            'id' => $snippet->id,
        ]);
    }

    public function test_admin_script_create_rejects_required_provider_and_content_and_invalid_position(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->from('/admin/scripts/create')->post('/admin/scripts', [
            'name' => 'Broken snippet',
            'position' => 'footer_middle',
            'provider' => '',
            'content' => '',
            'is_active' => '1',
        ]);

        $response
            ->assertRedirect('/admin/scripts/create')
            ->assertSessionHasErrors(['provider', 'content', 'position']);

        $this->assertDatabaseMissing('script_snippets', [
            'name' => 'Broken snippet',
        ]);
    }

    public function test_admin_index_and_edit_escape_administrative_values(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $name = '<script>window.adminNameExecuted = true;</script>';
        $provider = '<img src=x onerror="window.adminProviderExecuted=true">';
        $notes = '<iframe srcdoc="<script>window.adminNotesExecuted=true</script>"></iframe>';

        $snippet = ScriptSnippet::create([
            'name' => $name,
            'provider' => $provider,
            'position' => 'head_end',
            'content' => '<script>window.publicSnippet = true;</script>',
            'is_active' => true,
            'notes' => $notes,
        ]);

        $this->actingAs($admin)
            ->get('/admin/scripts')
            ->assertOk()
            ->assertSee(e($name), false)
            ->assertSee(e($provider), false)
            ->assertDontSee($name, false)
            ->assertDontSee($provider, false);

        $this->actingAs($admin)
            ->get("/admin/scripts/{$snippet->id}/edit")
            ->assertOk()
            ->assertSee(e($name), false)
            ->assertSee(e($provider), false)
            ->assertSee(e($notes), false)
            ->assertDontSee($name, false)
            ->assertDontSee($provider, false)
            ->assertDontSee($notes, false);
    }
}
