<?php

namespace App\Services;

class RobotsBuilder
{
    public function text(): string
    {
        $sections = [
            [
                'User-agent: *',
                'Disallow: /admin',
                'Allow: /',
            ],
            [
                'User-agent: GPTBot',
                'Disallow: /admin',
                'Allow: /',
            ],
            [
                'User-agent: OAI-SearchBot',
                'Disallow: /admin',
                'Allow: /',
            ],
            [
                'User-agent: Google-Extended',
                'Disallow: /admin',
                'Allow: /',
            ],
            [
                'User-agent: PerplexityBot',
                'Disallow: /admin',
                'Allow: /',
            ],
            [
                'User-agent: ClaudeBot',
                'Disallow: /admin',
                'Allow: /',
            ],
        ];

        return collect($sections)
            ->map(fn (array $lines): string => implode("\n", $lines))
            ->implode("\n\n")
            ."\n\nSitemap: {$this->url('/sitemap.xml')}\n";
    }

    private function url(string $path): string
    {
        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }
}
