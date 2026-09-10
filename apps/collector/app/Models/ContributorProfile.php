<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributorProfile extends Model
{
    protected $fillable = ['user_id', 'locality_id', 'locality_other', 'fluency_level', 'can_write_san'];
    protected function casts(): array { return ['can_write_san' => 'boolean']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function locality(): BelongsTo { return $this->belongsTo(Locality::class); }
}
