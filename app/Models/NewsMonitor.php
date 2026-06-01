<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'slug',
    'keywords',
    'language',
    'country',
    'max_results',
    'is_active',
])]
class NewsMonitor extends Model
{
    /**
     * @param  Builder<NewsMonitor>  $query
     * @return Builder<NewsMonitor>
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
            'max_results' => 'integer',
        ];
    }
}
