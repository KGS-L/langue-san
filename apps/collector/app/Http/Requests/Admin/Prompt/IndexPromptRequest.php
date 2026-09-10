<?php

namespace App\Http\Requests\Admin\Prompt;

use App\Enums\PromptType;
use App\Models\Prompt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Prompt::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'type' => ['nullable', Rule::enum(PromptType::class)],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'under_covered' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', Rule::in([20, 50, 100])],
        ];
    }
}
