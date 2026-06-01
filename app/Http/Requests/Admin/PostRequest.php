<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $post = $this->route('post');

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('posts', 'slug')->ignore($post?->id),
            ],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'cover_image_path' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'scheduled'])],
            'featured' => ['sometimes', 'boolean'],
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
            'key_takeaways.*' => ['required', 'string', 'max:255'],
            'entities' => ['nullable', 'array'],
            'entities.*' => ['required', 'string', 'max:120'],
            'faq_items' => ['nullable', 'array'],
            'faq_items.*' => ['required', 'array:question,answer'],
            'faq_items.*.question' => ['required', 'string', 'max:255'],
            'faq_items.*.answer' => ['required', 'string', 'max:2000'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['nullable', 'integer', 'exists:tags,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['nullable', 'integer', 'exists:tags,id'],
        ];
    }
}
