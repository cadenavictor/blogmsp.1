<?php

namespace Tests\Feature;

use App\Models\NewsMonitor;
use App\Models\User;
use App\Services\GoogleNewsClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleNewsTest extends TestCase
{
    use RefreshDatabase;

    private function fakeFeed(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Google News</title>
    <item>
      <title>IA transforma o jornalismo brasileiro - Portal Tech</title>
      <link>https://news.google.com/articles/abc123</link>
      <pubDate>Mon, 01 Jun 2026 10:00:00 GMT</pubDate>
      <description>&lt;a href="https://portaltech.com/ia"&gt;IA transforma o jornalismo&lt;/a&gt; novidades</description>
      <source url="https://portaltech.com">Portal Tech</source>
    </item>
    <item>
      <title>Startups apostam em modelos de linguagem - Jornal X</title>
      <link>https://news.google.com/articles/def456</link>
      <pubDate>Mon, 01 Jun 2026 09:00:00 GMT</pubDate>
      <description>&lt;a href="https://jornalx.com/llm"&gt;Startups apostam&lt;/a&gt;</description>
      <source url="https://jornalx.com">Jornal X</source>
    </item>
  </channel>
</rss>
XML;

        Http::fake([
            'news.google.com/*' => Http::response($xml, 200, ['Content-Type' => 'application/rss+xml']),
        ]);
    }

    public function test_service_parses_and_normalizes_items(): void
    {
        $this->fakeFeed();

        $items = app(GoogleNewsClient::class)->fetch('inteligencia artificial', 'pt-BR', 'BR', 20);

        $this->assertCount(2, $items);
        $this->assertSame('IA transforma o jornalismo brasileiro', $items[0]['title']);
        $this->assertSame('Portal Tech', $items[0]['source']);
        $this->assertSame('https://news.google.com/articles/abc123', $items[0]['link']);
        $this->assertNotNull($items[0]['published_at']);
    }

    public function test_service_respects_limit(): void
    {
        $this->fakeFeed();

        $items = app(GoogleNewsClient::class)->fetch('ia', 'pt-BR', 'BR', 1);

        $this->assertCount(1, $items);
    }

    public function test_admin_can_view_monitor_results(): void
    {
        $this->fakeFeed();
        $admin = User::factory()->create(['is_admin' => true]);
        $monitor = NewsMonitor::create([
            'name' => 'IA',
            'slug' => 'ia',
            'keywords' => 'inteligencia artificial',
            'language' => 'pt-BR',
            'country' => 'BR',
            'max_results' => 20,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get("/admin/news/{$monitor->id}")
            ->assertOk()
            ->assertSee('IA transforma o jornalismo brasileiro')
            ->assertSee('Portal Tech');
    }

    public function test_admin_can_create_monitor(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/news', [
            'name' => 'Economia',
            'slug' => '',
            'keywords' => 'economia brasil',
            'language' => 'pt-BR',
            'country' => 'BR',
            'max_results' => 15,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('news_monitors', [
            'slug' => 'economia',
            'keywords' => 'economia brasil',
        ]);
    }
}
