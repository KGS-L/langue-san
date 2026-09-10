<?php

namespace App\Http\Requests\Admin\Locality;

use App\Models\Locality;
use Illuminate\Foundation\Http\FormRequest;

class StoreLocalityRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Locality::class) ?? false; }
    public function rules(): array { return ['name'=>['required','string','max:150'],'province'=>['nullable','string','max:150'],'region'=>['nullable','string','max:150'],'suggested_variety_id'=>['nullable','exists:varieties,id'],'notes'=>['nullable','string','max:1500'],'is_active'=>['required','boolean']]; }
}
