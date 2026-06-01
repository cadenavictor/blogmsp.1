<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * Authorization is handled by the api.key middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['nullable'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'scheduled'])],
            'featured' => ['nullable', 'boolean'],
            'cover_image_url' => ['nullable', 'string', 'max:2048'],
            'published_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'meta_robots' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'ai_summary' => ['nullable', 'string'],
            'key_takeaways' => ['nullable', 'array'],
            'key_takeaways.*' => ['string', 'max:255'],
            'entities' => ['nullable', 'array'],
            'entities.*' => ['string', 'max:120'],
            'faq_items' => ['nullable', 'array'],
            'faq_items.*.question' => ['required_with:faq_items', 'string', 'max:255'],
            'faq_items.*.answer' => ['required_with:faq_items', 'string', 'max:2000'],
        ];
    }
}
