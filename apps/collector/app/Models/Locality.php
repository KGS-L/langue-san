<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Locality extends Model
{
    protected $fillable = [
        'name',
        'province',
        'region',
        'suggested_variety_id',
        'suggested_variety_status',
        'suggested_variety_source',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function suggestedVariety(): BelongsTo { return $this->belongsTo(Variety::class, 'suggested_variety_id'); }
    public function profiles(): HasMany { return $this->hasMany(ContributorProfile::class); }
    public function contributions(): HasMany { return $this->hasMany(Contribution::class); }
}
