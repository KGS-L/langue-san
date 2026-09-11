<?php

namespace App\Http\Requests\Contributor;

use App\Enums\ProjectContributionArea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isContributor() ?? false;
    }

    public function rules(): array
    {
        return [
            'contribution_areas' => ['required', 'array', 'min:1', 'max:5'],
            'contribution_areas.*' => ['required', Rule::enum(ProjectContributionArea::class)],
            'experience' => ['required', 'string', 'min:30', 'max:3000'],
            'motivation' => ['required', 'string', 'min:30', 'max:3000'],
            'availability' => ['nullable', Rule::in(['lt2', '2-5', '5-10', '10plus'])],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'san_connection' => ['nullable', 'string', 'max:1500'],
        ];
    }
}
