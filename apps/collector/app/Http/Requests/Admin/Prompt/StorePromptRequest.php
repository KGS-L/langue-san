<?php

namespace App\Http\Requests\Admin\Prompt;

use App\Enums\PromptType;
use App\Models\Prompt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePromptRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('create', Prompt::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9-]+$/', 'unique:prompts,code'],
            'category_id' => ['required', 'exists:categories,id'],
            'french_text' => ['required', 'string', 'max:1000'],
            'context' => ['nullable', 'string', 'max:1500'],
            'type' => ['required', Rule::enum(PromptType::class)],
            'difficulty' => ['required', 'integer', 'between:1,5'],
            'priority' => ['required', 'integer', 'between:0,100'],
            'target_contributions' => ['required', 'integer', 'between:1,100'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
