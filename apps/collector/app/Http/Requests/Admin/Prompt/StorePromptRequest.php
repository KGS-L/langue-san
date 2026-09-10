<?php

namespace App\Http\Requests\Admin\Prompt;

use App\Enums\PromptType;
use App\Models\Prompt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePromptRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Prompt::class) ?? false; }
    public function rules(): array { return ['category_id'=>['required','exists:categories,id'],'french_text'=>['required','string','max:1000'],'type'=>['required',Rule::enum(PromptType::class)],'difficulty'=>['required','integer','between:1,5'],'target_contributions'=>['required','integer','between:1,100'],'is_active'=>['required','boolean']]; }
}
