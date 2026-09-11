<?php

namespace App\Http\Requests\Contributor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $choice = $this->input('locality_choice');

        $this->merge([
            'locality_id' => is_numeric($choice) ? (int) $choice : null,
            'locality_other' => $choice === 'other'
                ? $this->input('locality_other')
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'locality_choice' => [
                'required',
                Rule::when(
                    $this->input('locality_choice') === 'other',
                    ['in:other'],
                    ['integer', Rule::exists('localities', 'id')->where('is_active', true)],
                ),
            ],
            'locality_id' => ['nullable', 'integer', Rule::exists('localities', 'id')->where('is_active', true)],
            'locality_other' => ['nullable', 'string', 'max:150', 'required_if:locality_choice,other'],
            'fluency_level' => ['required', 'in:native,fluent,intermediate,basic'],
            'can_write_san' => ['required', 'boolean'],
            'next' => ['nullable', Rule::in(['translation', 'natural-speech'])],
        ];
    }

    public function messages(): array
    {
        return [
            'locality_choice.required' => 'Veuillez sélectionner votre ville ou localité.',
            'locality_choice.in' => 'La localité sélectionnée est invalide.',
            'locality_choice.integer' => 'La localité sélectionnée est invalide.',
            'locality_choice.exists' => 'La localité sélectionnée est invalide ou inactive.',
            'locality_other.required_if' => 'Veuillez préciser votre localité.',
            'fluency_level.required' => 'Indiquez votre niveau de pratique du San.',
            'can_write_san.required' => 'Indiquez si vous savez écrire le San.',
        ];
    }
}
