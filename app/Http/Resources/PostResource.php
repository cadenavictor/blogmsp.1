<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 */
class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'url' => route('posts.show', $this->resource),
            'status' => $this->status,
            'featured' => (bool) $this->featured,
            'excerpt' => $this->excerpt,
            'reading_time' => $this->reading_time,
            'cover_image' => $this->cover_image_path,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ]),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')->all()),
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
                'canonical_url' => $this->canonical_url,
                'meta_robots' => $this->meta_robots,
                'og_title' => $this->og_title,
                'og_description' => $this->og_description,
            ],
            'ai_summary' => $this->ai_summary,
            'key_takeaways' => $this->key_takeaways,
            'entities' => $this->entities,
            'faq_items' => $this->faq_items,
            'published_at' => $this->published_at?->toIso8601String(),
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
