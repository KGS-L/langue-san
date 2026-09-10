<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributorConsent extends Model
{
    protected $fillable = [
        'contributor_profile_id',
        'consent_version_id',
        'accepted_at',
        'ip_hash',
    ];

    protected function casts(): array
    {
        return ['accepted_at' => 'datetime'];
    }

    public function contributorProfile(): BelongsTo { return $this->belongsTo(ContributorProfile::class); }
    public function consentVersion(): BelongsTo { return $this->belongsTo(ConsentVersion::class); }
}
