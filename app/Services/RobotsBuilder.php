<?php

namespace App\Services;

use App\Models\SiteSetting;

class RobotsBuilder
{
    public function text(): string
    {
        $settings = SiteSetting::current();
        $training = $settings->allow_ai_training;
        $search = $settings->allow_ai_search;

        $sections = [
            $this->section('*', true),
            // AI training crawlers
            $this->section('GPTBot', $training),
            // AI search / citation crawlers
            $this->section('OAI-SearchBot', $search),
            $this->section('Google-Extended', $training),
            $this->section('PerplexityBot', $search),
            $this->section('ClaudeBot', $training),
        ];

        return collect($sections)
            ->map(fn (array $lines): string => implode("\n", $lines))
            ->implode("\n\n")
            ."\n\nSitemap: {$this->url('/sitemap.xml')}\n";
    }

    /**
     * @return array<int, string>
     */
    private function section(string $userAgent, bool $allowed): array
    {
        if (! $allowed) {
            return [
                "User-agent: {$userAgent}",
                'Disallow: /',
            ];
        }

        return [
            "User-agent: {$userAgent}",
            'Disallow: /admin',
            'Allow: /',
        ];
    }

    private function url(string $path): string
    {
        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }
}
