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
            'locale' => 'pt_BR',
            'language' => 'pt-BR',
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

        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): self => static::query()->orderBy('id')->first() ?? new self(static::defaults()),
        );
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function siteName(): string
    {
        return $this->nonEmpty($this->site_name) ?? (string) config('app.name', 'Blog MSP');
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
}
