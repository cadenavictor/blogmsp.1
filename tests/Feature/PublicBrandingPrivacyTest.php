<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicBrandingPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_uses_melhores_de_sao_paulo_identity_and_lgpd_cookie_notice(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSee('Melhores de São Paulo')
            ->assertSee('Curadoria independente')
            ->assertSee('data-cookie-banner', false)
            ->assertDontSee('Anúncio');

        $html = $response->getContent();
        $header = $this->htmlBetween($html, '<header', '</header>');
        $footer = $this->htmlBetween($html, '<footer', '</footer>');

        $this->assertStringNotContainsString('Política de Privacidade', $header);
        $this->assertStringNotContainsString('LGPD', $header);
        $this->assertStringNotContainsString('cookies', $header);

        $this->assertStringContainsString('Política de Privacidade', $footer);
        $this->assertStringContainsString('LGPD e cookies', $footer);
        $this->assertStringContainsString('Aviso de cookies', $footer);
        $this->assertStringContainsString('data-cookie-banner', $footer);
    }

    public function test_public_layout_recovers_from_stale_site_settings_cache(): void
    {
        Cache::forever('site_settings.current', unserialize('O:18:"MissingSiteSetting":0:{}'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Melhores de São Paulo');
    }

    public function test_privacy_policy_page_is_available_and_linked_for_lgpd(): void
    {
        $this->get('/privacidade')
            ->assertOk()
            ->assertSee('Política de Privacidade')
            ->assertSee('Lei Geral de Proteção de Dados')
            ->assertSee('cookies essenciais')
            ->assertSee('Melhores de São Paulo');
    }

    public function test_privacy_page_is_in_pages_sitemap(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);

        $this->get('/sitemap-pages.xml')
            ->assertOk()
            ->assertSee('http://127.0.0.1:8000/privacidade', false);
    }

    public function test_home_keeps_editorial_post_cards_with_local_service_positioning(): void
    {
        $author = User::factory()->create(['name' => 'Redação SP']);
        $category = Category::create(['name' => 'Serviços', 'slug' => 'servicos']);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Como escolher empresas de limpeza em São Paulo',
            'slug' => 'empresas-limpeza-sao-paulo',
            'status' => 'published',
            'content' => 'Guia editorial de teste.',
            'excerpt' => 'Critérios para comparar atendimento, preço e reputação.',
            'published_at' => now(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Últimas análises')
            ->assertSee('Como escolher empresas de limpeza em São Paulo')
            ->assertSee('Critérios para comparar atendimento, preço e reputação.');
    }

    private function htmlBetween(string $html, string $start, string $end): string
    {
        $startPosition = strpos($html, $start);
        $endPosition = strpos($html, $end, $startPosition === false ? 0 : $startPosition);

        if ($startPosition === false || $endPosition === false) {
            return '';
        }

        return substr($html, $startPosition, $endPosition - $startPosition + strlen($end));
    }
}
