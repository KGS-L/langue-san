<?php

namespace App\Http\Requests\Contributor;

use App\Enums\AgeRange;
use App\Enums\ProfessionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isContributor() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'country' => ['required', 'string', 'max:120'],
            'age_range' => ['required', Rule::enum(AgeRange::class)],
            'profession' => ['required', Rule::enum(ProfessionType::class)],
            'profession_other' => [
                Rule::requiredIf(fn () => $this->input('profession') === ProfessionType::OTHER->value),
                'nullable', 'string', 'max:150',
            ],
            'organization' => ['nullable', 'string', 'max:180'],
        ];
    }
}
