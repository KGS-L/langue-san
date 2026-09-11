<?php

namespace App\Http\Requests\Contributor;

use Illuminate\Foundation\Http\FormRequest;

class StartNaturalSpeechRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prompt_id' => ['required', 'integer', 'exists:prompts,id'],
            'consent' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'prompt_id.required' => 'Choisissez un sujet de parole naturelle.',
            'prompt_id.exists' => 'Le sujet sélectionné n’est plus disponible.',
            'consent.required' => 'Vous devez accepter le consentement pour commencer la collecte.',
            'consent.accepted' => 'Vous devez accepter le consentement pour commencer la collecte.',
        ];
    }
}
