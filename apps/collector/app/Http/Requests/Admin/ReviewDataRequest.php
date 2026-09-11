<?php

namespace App\Http\Requests\Admin;

use App\Enums\DataRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('review data requests') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(DataRequestStatus::class)],
            'resolution_notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
