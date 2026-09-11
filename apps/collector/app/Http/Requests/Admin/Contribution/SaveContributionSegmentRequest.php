<?php

namespace App\Http\Requests\Admin\Contribution;

use Illuminate\Foundation\Http\FormRequest;

class SaveContributionSegmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $contribution = $this->route('contribution');
        return $contribution && ($this->user()?->can('update', $contribution) ?? false);
    }

    public function rules(): array
    {
        return [
            'start_ms' => ['nullable', 'integer', 'min:0', 'max:3600000'],
            'end_ms' => ['nullable', 'integer', 'min:0', 'max:3600000', 'gt:start_ms'],
            'san_text' => ['required', 'string', 'max:10000'],
            'french_translation' => ['required', 'string', 'max:10000'],
            'variety_id' => ['nullable', 'integer', 'exists:varieties,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'san_text.required' => 'La transcription San du segment est obligatoire.',
            'french_translation.required' => 'La traduction française du segment est obligatoire.',
            'end_ms.gt' => 'La fin du segment doit être postérieure à son début.',
        ];
    }
}
