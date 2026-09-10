<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variety extends Model
{
    protected $fillable = ['name', 'iso_code', 'description', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function localities(): HasMany { return $this->hasMany(Locality::class, 'suggested_variety_id'); }
    public function validations(): HasMany { return $this->hasMany(Validation::class); }
}
