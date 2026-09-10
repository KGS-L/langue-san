<?php

namespace App\Http\Requests\Contributor;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn (Builder $query) => $query->where('is_active', true)),
            ],
            'consent' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Veuillez choisir un thème.',
            'category_id.exists' => 'Le thème sélectionné n’est pas disponible.',
            'consent.required' => 'Vous devez accepter le consentement pour commencer la collecte.',
            'consent.accepted' => 'Vous devez accepter le consentement pour commencer la collecte.',
        ];
    }
}
