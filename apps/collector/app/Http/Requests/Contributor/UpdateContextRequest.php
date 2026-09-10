<?php

namespace App\Http\Requests\Contributor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locality_id' => ['nullable', 'integer', 'exists:localities,id', 'required_without:locality_other'],
            'locality_other' => ['nullable', 'string', 'max:150', 'required_without:locality_id'],
            'fluency_level' => ['required', 'in:native,fluent,intermediate,basic'],
            'can_write_san' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'locality_id.required_without' => 'Choisissez une localité ou précisez-en une autre.',
            'locality_other.required_without' => 'Précisez votre localité si elle ne figure pas dans la liste.',
            'fluency_level.required' => 'Indiquez votre niveau de pratique du San.',
            'can_write_san.required' => 'Indiquez si vous savez écrire le San.',
        ];
    }
}
