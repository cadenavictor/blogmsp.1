<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

#[Fillable([
    'site_name',
    'tagline',
    'home_title',
    'home_meta_description',
    'default_meta_description',
    'default_og_image',
    'logo_path',
    'favicon_path',
    'organization_name',
    'organization_description',
    'contact_email',
    'contact_phone',
    'twitter_handle',
    'social_links',
    'locale',
    'language',
    'llms_summary',
    'ai_policy',
    'allow_ai_training',
    'allow_ai_search',
])]
class SiteSetting extends Model
{
    private const CACHE_KEY = 'site_settings.current';

    /**
     * @return array<string, mixed>
     */
    protected static function defaults(): array
    {
        return [
            'site_name' => 'Melhores de São Paulo',
            'tagline' => 'Curadoria independente de empresas, serviços e experiências em São Paulo.',
            'home_title' => 'Melhores de São Paulo',
            'home_meta_description' => 'Reviews, guias e notícias para comparar empresas, serviços e experiências na cidade de São Paulo com critérios editoriais claros.',
            'default_meta_description' => 'Melhores de São Paulo reúne análises independentes, guias práticos e notícias sobre empresas e serviços da capital paulista.',
            'organization_name' => 'Melhores de São Paulo',
            'organization_description' => 'Blog editorial independente sobre empresas, serviços, bairros e experiências relevantes na cidade de São Paulo.',
            'locale' => 'pt_BR',
            'language' => 'pt-BR',
            'llms_summary' => 'Melhores de São Paulo publica reviews, guias, dicas e notícias sobre empresas e serviços da cidade de São Paulo, com foco em critérios claros, contexto local e respostas úteis para mecanismos de busca e agentes de IA.',
            'allow_ai_training' => true,
            'allow_ai_search' => true,
        ];
    }

    /**
     * The single settings row (cached). Returns an unsaved instance with
     * sensible defaults when the table/row does not exist yet.
     */
    public static function current(): self
    {
        if (! Schema::hasTable('site_settings')) {
            return new self(static::defaults());
        }

        $cached = Cache::get(self::CACHE_KEY);

        if ($cached instanceof self) {
            return static::withDefaults($cached);
        }

        if ($cached !== null) {
            Cache::forget(self::CACHE_KEY);
        }

        $settings = static::withDefaults(static::query()->orderBy('id')->first() ?? new self());
        Cache::forever(self::CACHE_KEY, $settings);

        return $settings;
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function siteName(): string
    {
        return $this->nonEmpty($this->site_name) ?? static::defaults()['site_name'];
    }

    public function organizationName(): string
    {
        return $this->nonEmpty($this->organization_name) ?? $this->siteName();
    }

    public function siteUrl(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    /**
     * Non-empty social profile URLs for schema.org sameAs.
     *
     * @return array<int, string>
     */
    public function sameAs(): array
    {
        $links = is_array($this->social_links) ? $this->social_links : [];

        return collect($links)
            ->map(fn ($url): string => is_string($url) ? trim($url) : '')
            ->filter(fn (string $url): bool => $url !== '')
            ->values()
            ->all();
    }

    /**
     * Resolve a stored image reference (path or absolute URL) to a full URL.
     */
    public function imageUrl(?string $path): ?string
    {
        $path = $this->nonEmpty($path);

        if ($path === null) {
            return null;
        }

        if (str_starts_with($path, '//')) {
            return 'https:'.$path;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'uploads/') || str_starts_with($path, '/uploads/') || str_starts_with($path, 'images/') || str_starts_with($path, '/images/')) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'allow_ai_training' => 'boolean',
            'allow_ai_search' => 'boolean',
        ];
    }

    private function nonEmpty(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function withDefaults(self $settings): self
    {
        foreach (static::defaults() as $key => $value) {
            if (blank($settings->getAttribute($key))) {
                $settings->setAttribute($key, $value);
            }
        }

        return $settings;
    }
}
