<?php

namespace App\Http\Requests\Admin\ProjectApplication;

use App\Enums\ProjectApplicationStatus;
use App\Models\ProjectApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewProjectApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('review project applications') ?? false;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in([
                ProjectApplicationStatus::APPROVED->value,
                ProjectApplicationStatus::REJECTED->value,
            ])],
            'decision_reason' => [
                Rule::requiredIf(fn () => $this->input('decision') === ProjectApplicationStatus::REJECTED->value),
                'nullable', 'string', 'max:2000',
            ],
        ];
    }
}
