<?php

namespace App\Http\Requests\Admin\Variety;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVarietyRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update', $this->route('variety')) ?? false; }
    public function rules(): array { $v=$this->route('variety'); return ['name'=>['required','string','max:120',Rule::unique('varieties','name')->ignore($v->id)],'iso_code'=>['nullable','string','max:10',Rule::unique('varieties','iso_code')->ignore($v->id)],'description'=>['nullable','string','max:1500'],'is_active'=>['required','boolean']]; }
}
