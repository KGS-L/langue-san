<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserConsent extends Model
{
    protected $fillable = ['user_id', 'consent_version_id', 'accepted_at', 'ip_hash'];
    protected function casts(): array { return ['accepted_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function consentVersion(): BelongsTo { return $this->belongsTo(ConsentVersion::class); }
}
