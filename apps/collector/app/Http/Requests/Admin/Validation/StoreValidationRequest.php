<?php

namespace App\Http\Requests\Admin\Validation;

use App\Enums\ValidationDecision;
use App\Models\Validation as ValidationModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreValidationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ValidationModel::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::enum(ValidationDecision::class)],
            'san_text_corrected' => ['nullable', 'required_if:decision,correct', 'string', 'max:5000'],
            'variety_id' => ['nullable', 'required_unless:decision,reject', 'exists:varieties,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $contribution = $this->route('contribution');

            if ($contribution && ValidationModel::query()
                ->where('contribution_id', $contribution->id)
                ->where('validator_id', $this->user()->id)
                ->exists()) {
                $validator->errors()->add('decision', 'Vous avez déjà validé cette contribution. Une deuxième validation doit être faite par une autre personne.');
            }
        }];
    }
}
