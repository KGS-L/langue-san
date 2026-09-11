<?php

namespace App\Http\Requests\Admin\Contribution;

use App\Enums\PromptType;
use Illuminate\Foundation\Http\FormRequest;

class TranscribeContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('contribution')) ?? false;
    }

    public function rules(): array
    {
        $contribution = $this->route('contribution');
        $contribution?->loadMissing('prompt');
        $max = $contribution?->prompt?->type === PromptType::NARRATIVE ? 20000 : 5000;

        return ['san_text' => ['required', 'string', 'max:'.$max]];
    }
}
