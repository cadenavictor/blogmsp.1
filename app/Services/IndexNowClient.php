<?php

namespace App\Services;

use App\Models\IndexNowSubmission;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class IndexNowClient
{
    private const ENDPOINT = 'https://api.indexnow.org/indexnow';
    private const RESPONSE_BODY_LIMIT = 4000;

    /**
     * @param  array<int, string>  $urls
     */
    public function submit(array $urls): ?Response
    {
        $urls = array_values($urls);

        $payload = [
            'host' => $this->host(),
            'key' => $this->key(),
            'keyLocation' => $this->keyLocation(),
            'urlList' => $urls,
        ];

        try {
            $response = Http::post(self::ENDPOINT, $payload);
        } catch (Throwable $exception) {
            $this->recordSubmissions(
                $urls,
                null,
                $exception::class.': '.$exception->getMessage()
            );

            return null;
        }

        $this->recordSubmissions($urls, $response->status(), $response->body());

        return $response;
    }

    public function key(): string
    {
        $key = trim((string) config('services.indexnow.key', 'local-indexnow-key'));

        if ($key === '') {
            $key = 'local-indexnow-key';
        }

        if (app()->isProduction() && $key === 'local-indexnow-key') {
            throw new RuntimeException('INDEXNOW_KEY must be configured with a non-default value in production.');
        }

        return $key;
    }

    private function keyLocation(): string
    {
        return rtrim((string) config('app.url'), '/').'/'.$this->key().'.txt';
    }

    private function host(): string
    {
        $appUrl = (string) config('app.url');
        $host = parse_url($appUrl, PHP_URL_HOST) ?: $appUrl;
        $port = parse_url($appUrl, PHP_URL_PORT);

        return $port ? "{$host}:{$port}" : $host;
    }

    /**
     * @param  array<int, string>  $urls
     */
    private function recordSubmissions(array $urls, ?int $statusCode, ?string $responseBody): void
    {
        foreach ($urls as $url) {
            IndexNowSubmission::create([
                'url' => $url,
                'status_code' => $statusCode,
                'response_body' => $this->truncateResponseBody($responseBody),
                'submitted_at' => now(),
            ]);
        }
    }

    private function truncateResponseBody(?string $responseBody): ?string
    {
        if ($responseBody === null) {
            return null;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($responseBody, 0, self::RESPONSE_BODY_LIMIT, 'UTF-8');
        }

        if (preg_match_all('/./us', $responseBody, $characters) !== false) {
            return implode('', array_slice($characters[0], 0, self::RESPONSE_BODY_LIMIT));
        }

        $asciiOnly = preg_replace('/[^\x00-\x7F]/', '', $responseBody);

        if (! is_string($asciiOnly)) {
            return '';
        }

        return substr($asciiOnly, 0, self::RESPONSE_BODY_LIMIT);
    }
}
