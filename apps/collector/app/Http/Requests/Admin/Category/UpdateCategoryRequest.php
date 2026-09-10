<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update', $this->route('category')) ?? false; }
    public function rules(): array { $category=$this->route('category'); return ['name'=>['required','string','max:120',Rule::unique('categories','name')->ignore($category->id)],'description'=>['nullable','string','max:1000'],'icon'=>['nullable','string','max:100'],'display_order'=>['required','integer','min:0'],'is_active'=>['required','boolean']]; }
}
