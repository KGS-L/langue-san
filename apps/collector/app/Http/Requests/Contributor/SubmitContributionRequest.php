<?php

namespace App\Http\Requests\Contributor;

use App\Enums\PromptType;
use Illuminate\Foundation\Http\FormRequest;

class SubmitContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sessionPrompt = $this->route('sessionPrompt');
        $sessionPrompt?->loadMissing('prompt');
        $isNarrative = $sessionPrompt?->prompt?->type === PromptType::NARRATIVE;

        return [
            'san_text' => $isNarrative
                ? ['nullable', 'string', 'max:20000']
                : ['nullable', 'string', 'max:5000', 'required_without:audio'],
            'audio' => [
                $isNarrative ? 'required' : 'nullable',
                'file',
                'max:51200',
                'mimetypes:audio/mpeg,audio/mp4,audio/wav,audio/x-wav,audio/webm,audio/ogg,video/webm',
                ...($isNarrative ? [] : ['required_without:san_text']),
            ],
            'audio_duration_ms' => ['nullable', 'integer', 'min:0', 'max:3600000'],
        ];
    }

    public function messages(): array
    {
        return [
            'san_text.required_without' => 'Écrivez une réponse en San ou enregistrez votre voix.',
            'audio.required' => 'Un enregistrement audio est nécessaire pour une contribution de parole naturelle.',
            'audio.required_without' => 'Écrivez une réponse en San ou enregistrez votre voix.',
            'audio.max' => 'L’enregistrement audio ne doit pas dépasser 50 Mo.',
            'audio.mimetypes' => 'Le format de l’enregistrement audio n’est pas pris en charge.',
        ];
    }
}
