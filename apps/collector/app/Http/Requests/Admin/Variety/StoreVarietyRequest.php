<?php

namespace App\Http\Requests\Admin\Variety;

use App\Models\Variety;
use Illuminate\Foundation\Http\FormRequest;

class StoreVarietyRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Variety::class) ?? false; }
    public function rules(): array { return ['name'=>['required','string','max:120','unique:varieties,name'],'iso_code'=>['nullable','string','max:10','unique:varieties,iso_code'],'description'=>['nullable','string','max:1500'],'is_active'=>['required','boolean']]; }
}
