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
    'notes',
])]
class ScriptSnippet extends Model
{
    public const POSITIONS = [
        'head_start',
        'head_end',
        'body_start',
        'body_end',
    ];

    /**
     * @return array<int, string>
     */
    public static function positions(): array
    {
        return self::POSITIONS;
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
        ];
    }
}
