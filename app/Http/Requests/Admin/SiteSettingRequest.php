<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allow_ai_training' => $this->boolean('allow_ai_training'),
            'allow_ai_search' => $this->boolean('allow_ai_search'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'home_title' => ['nullable', 'string', 'max:255'],
            'home_meta_description' => ['nullable', 'string', 'max:320'],
            'default_meta_description' => ['nullable', 'string', 'max:320'],
            'default_og_image' => ['nullable', 'string', 'max:2048'],
            'logo_path' => ['nullable', 'string', 'max:2048'],
            'favicon_path' => ['nullable', 'string', 'max:2048'],

            'organization_name' => ['nullable', 'string', 'max:255'],
            'organization_description' => ['nullable', 'string', 'max:1000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:60'],
            'twitter_handle' => ['nullable', 'string', 'max:60'],
            'social_links' => ['nullable', 'array'],
            'social_links.*' => ['nullable', 'url', 'max:2048'],

            'locale' => ['nullable', 'string', 'max:16'],
            'language' => ['nullable', 'string', 'max:16'],
            'llms_summary' => ['nullable', 'string', 'max:2000'],
            'ai_policy' => ['nullable', 'string', 'max:1000'],
            'allow_ai_training' => ['boolean'],
            'allow_ai_search' => ['boolean'],
        ];
    }
}
