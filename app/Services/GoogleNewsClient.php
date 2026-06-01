<?php

namespace App\Services;

use App\Models\NewsMonitor;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class GoogleNewsClient
{
    private const ENDPOINT = 'https://news.google.com/rss/search';

    /**
     * Fetch and normalize Google News results for a saved monitor.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchForMonitor(NewsMonitor $monitor): array
    {
        return $this->fetch(
            $monitor->keywords,
            $monitor->language,
            $monitor->country,
            $monitor->max_results,
        );
    }

    /**
     * Fetch and normalize Google News RSS results for a free-form query.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetch(string $query, ?string $language = null, ?string $country = null, int $limit = 20): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $language = $language ?: (string) config('services.google_news.language', 'pt-BR');
        $country = $country ?: (string) config('services.google_news.country', 'BR');
        $limit = max(1, min($limit, 50));

        $cacheKey = 'google_news:'.md5(implode('|', [$query, $language, $country, $limit]));
        $ttl = (int) config('services.google_news.cache_ttl', 900);

        return Cache::remember($cacheKey, $ttl, function () use ($query, $language, $country, $limit): array {
            return $this->request($query, $language, $country, $limit);
        });
    }

    /**
     * Build the public Google News RSS URL (useful for the UI / debugging).
     */
    public function feedUrl(string $query, string $language, string $country): string
    {
        return self::ENDPOINT.'?'.http_build_query([
            'q' => $query,
            'hl' => $language,
            'gl' => $country,
            'ceid' => $country.':'.explode('-', $language)[0],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function request(string $query, string $language, string $country, int $limit): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'BlogMSP/1.0 (+'.config('app.url').')',
                'Accept' => 'application/rss+xml, application/xml;q=0.9, */*;q=0.8',
            ])->timeout(12)->get($this->feedUrl($query, $language, $country));
        } catch (Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        return $this->parse($response->body(), $limit);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parse(string $xml, int $limit): array
    {
        $previous = libxml_use_internal_errors(true);

        try {
            $document = simplexml_load_string($xml);
        } catch (Throwable) {
            $document = false;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if ($document === false || ! isset($document->channel->item)) {
            return [];
        }

        $items = [];

        foreach ($document->channel->item as $item) {
            $rawTitle = trim((string) $item->title);
            [$title, $sourceFromTitle] = $this->splitTitleAndSource($rawTitle);

            $source = trim((string) $item->source);
            if ($source === '') {
                $source = $sourceFromTitle;
            }

            $items[] = [
                'title' => $title,
                'link' => trim((string) $item->link),
                'source' => $source,
                'published_at' => $this->parseDate((string) $item->pubDate),
                'snippet' => $this->cleanSnippet((string) $item->description),
            ];

            if (count($items) >= $limit) {
                break;
            }
        }

        return $items;
    }

    /**
     * Google News titles are usually "Headline - Source".
     *
     * @return array{0: string, 1: string}
     */
    private function splitTitleAndSource(string $rawTitle): array
    {
        $position = mb_strrpos($rawTitle, ' - ');

        if ($position === false) {
            return [$rawTitle, ''];
        }

        return [
            trim(mb_substr($rawTitle, 0, $position)),
            trim(mb_substr($rawTitle, $position + 3)),
        ];
    }

    private function parseDate(string $date): ?string
    {
        $date = trim($date);

        if ($date === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($date)->toIso8601String();
        } catch (Throwable) {
            return null;
        }
    }

    private function cleanSnippet(string $description): string
    {
        $text = strip_tags(html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        return mb_substr($text, 0, 280);
    }
}
