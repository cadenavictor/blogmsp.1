<?php

namespace App\Http\Requests\Admin;

use App\Models\ScriptSnippet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScriptSnippetRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'string', 'max:120'],
            'position' => ['required', Rule::in(ScriptSnippet::positions())],
            'content' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
