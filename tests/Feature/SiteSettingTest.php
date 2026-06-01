<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected(): void
    {
        $this->get('/admin/configuracoes/seo')->assertRedirect('/login');
    }

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/configuracoes/seo')->assertForbidden();
    }

    public function test_admin_can_view_settings_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/configuracoes/seo')
            ->assertOk()
            ->assertSee('SEO &amp; Site', false);
    }

    public function test_admin_can_update_settings_and_cache_refreshes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Prime the cache with the default (empty) settings.
        $this->assertNotSame('Meu Blog Tech', SiteSetting::current()->siteName());

        $this->actingAs($admin)->put('/admin/configuracoes/seo', [
            'site_name' => 'Meu Blog Tech',
            'home_meta_description' => 'A melhor fonte de tecnologia.',
            'llms_summary' => 'Blog sobre tecnologia e IA.',
            'logo_path' => 'https://cdn.example.com/logo.png',
            'favicon_path' => 'https://cdn.example.com/favicon.png',
            'social_links' => ['x' => 'https://x.com/meublog'],
            'allow_ai_training' => '1',
            'allow_ai_search' => '1',
        ])->assertRedirect('/admin/configuracoes/seo')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_settings', [
            'site_name' => 'Meu Blog Tech',
            'favicon_path' => 'https://cdn.example.com/favicon.png',
        ]);
        $this->assertSame('Meu Blog Tech', SiteSetting::current()->siteName());
        $this->assertSame(['https://x.com/meublog'], SiteSetting::current()->sameAs());
    }

    public function test_logo_and_favicon_render_on_public_site(): void
    {
        SiteSetting::create([
            'site_name' => 'Marca Visual',
            'logo_path' => 'https://cdn.example.com/logo.png',
            'favicon_path' => 'https://cdn.example.com/favicon.png',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="icon" href="https://cdn.example.com/favicon.png">', false)
            ->assertSee('<img class="brand-logo" src="https://cdn.example.com/logo.png"', false);
    }

    public function test_validation_rejects_invalid_email_and_social_url(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->from('/admin/configuracoes/seo')->put('/admin/configuracoes/seo', [
            'contact_email' => 'nao-e-email',
            'social_links' => ['x' => 'isto-nao-e-url'],
        ])->assertRedirect('/admin/configuracoes/seo')
            ->assertSessionHasErrors(['contact_email', 'social_links.x']);
    }
}
