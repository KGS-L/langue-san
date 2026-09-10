<?php

namespace App\Http\Requests\Admin\Validation;

use App\Enums\ValidationDecision;
use App\Models\Validation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreValidationRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Validation::class) ?? false; }
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::enum(ValidationDecision::class)],
            'san_text_corrected' => ['nullable','required_if:decision,correct','string','max:5000'],
            'variety_id' => ['nullable','required_unless:decision,reject','exists:varieties,id'],
            'notes' => ['nullable','string','max:2000'],
        ];
    }
}
