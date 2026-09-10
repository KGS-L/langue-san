<?php

namespace App\Http\Requests\Admin\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Category::class) ?? false; }
    public function rules(): array { return ['name'=>['required','string','max:120','unique:categories,name'],'description'=>['nullable','string','max:1000'],'icon'=>['nullable','string','max:100'],'display_order'=>['required','integer','min:0'],'is_active'=>['required','boolean']]; }
}
