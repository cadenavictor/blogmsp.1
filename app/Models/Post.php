<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'category_id',
    'title',
    'slug',
    'excerpt',
    'content',
    'cover_image_path',
    'status',
    'featured',
    'published_at',
    'scheduled_at',
    'seo_title',
    'seo_description',
    'canonical_url',
    'meta_robots',
    'og_title',
    'og_description',
    'ai_summary',
    'key_takeaways',
    'entities',
    'faq_items',
])]
class Post extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Public URL of the cover image, resolving stored paths (physical
     * public/uploads, public/images, storage/) or absolute URLs.
     *
     * @return Attribute<string|null, never>
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $path = trim((string) $this->cover_image_path);

            if ($path === '') {
                return null;
            }

            if (Str::startsWith($path, '//')) {
                return 'https:'.$path;
            }

            if (Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }

            if (Str::startsWith($path, ['uploads/', '/uploads/', 'images/', '/images/'])) {
                return asset(ltrim($path, '/'));
            }

            return asset('storage/'.ltrim($path, '/'));
        });
    }

    /**
     * Estimated reading time in minutes, derived from the content.
     *
     * @return Attribute<int, never>
     */
    protected function readingTime(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                $text = strip_tags((string) $this->content);
                $words = preg_match_all('/\p{L}+/u', $text);

                return max(1, (int) ceil(($words ?: 0) / 200));
            },
        )->shouldCache();
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'key_takeaways' => 'array',
            'entities' => 'array',
            'faq_items' => 'array',
        ];
    }
}
