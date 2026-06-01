<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsMonitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        $slug = trim((string) $this->input('slug'));

        if ($slug === '') {
            $slug = Str::slug((string) $this->input('name'));
        }

        $this->merge([
            'slug' => $slug,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $monitor = $this->route('news');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('news_monitors', 'slug')->ignore($monitor?->id),
            ],
            'keywords' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:10'],
            'country' => ['required', 'string', 'max:8'],
            'max_results' => ['required', 'integer', 'min:1', 'max:50'],
            'is_active' => ['boolean'],
        ];
    }
}
