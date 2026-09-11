<?php

namespace App\Http\Requests\Admin\Contribution;

use Illuminate\Foundation\Http\FormRequest;

class ContributionQueueFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view contributions') ?? false;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'locality_id' => ['nullable', 'integer', 'exists:localities,id'],
            'has_audio' => ['nullable', 'in:0,1'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }
}
