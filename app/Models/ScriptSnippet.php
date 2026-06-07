<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'provider',
    'position',
    'content',
    'is_active',
    'requires_consent',
    'cookie_category',
    'notes',
])]
class ScriptSnippet extends Model
{
    public const COOKIE_CATEGORY_ESSENTIAL = 'essential';

    public const COOKIE_CATEGORY_ANALYTICS = 'analytics';

    public const POSITIONS = [
        'head_start',
        'head_end',
        'body_start',
        'body_end',
    ];

    public const COOKIE_CATEGORIES = [
        self::COOKIE_CATEGORY_ESSENTIAL,
        self::COOKIE_CATEGORY_ANALYTICS,
    ];

    /**
     * @return array<int, string>
     */
    public static function positions(): array
    {
        return self::POSITIONS;
    }

    /**
     * @return array<int, string>
     */
    public static function cookieCategories(): array
    {
        return self::COOKIE_CATEGORIES;
    }

    /**
     * @param  Builder<ScriptSnippet>  $query
     * @return Builder<ScriptSnippet>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_consent' => 'boolean',
        ];
    }
}
