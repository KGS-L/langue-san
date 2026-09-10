<?php

namespace App\Http\Requests\Admin\Contribution;

use Illuminate\Foundation\Http\FormRequest;

class TranscribeContributionRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update', $this->route('contribution')) ?? false; }
    public function rules(): array { return ['san_text' => ['required','string','max:5000']]; }
}
