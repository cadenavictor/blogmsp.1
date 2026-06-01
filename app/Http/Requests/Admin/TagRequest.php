<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * Generate a slug from the name when one is not provided.
     */
    protected function prepareForValidation(): void
    {
        $slug = trim((string) $this->input('slug'));

        if ($slug === '') {
            $slug = Str::slug((string) $this->input('name'));
        }

        $this->merge(['slug' => $slug]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tag = $this->route('tag');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('tags', 'slug')->ignore($tag?->id),
            ],
            'description' => ['nullable', 'string'],
        ];
    }
}
