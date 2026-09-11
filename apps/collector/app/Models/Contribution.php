<?php

namespace App\Models;

use App\Enums\ContributionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Contribution extends Model
{
    protected $fillable = [
        'collection_session_id',
        'prompt_id',
        'contributor_profile_id',
        'locality_id',
        'san_text',
        'submitted_san_text',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContributionStatus::class,
            'submitted_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo { return $this->belongsTo(CollectionSession::class, 'collection_session_id'); }
    public function prompt(): BelongsTo { return $this->belongsTo(Prompt::class); }
    public function contributorProfile(): BelongsTo { return $this->belongsTo(ContributorProfile::class); }
    public function locality(): BelongsTo { return $this->belongsTo(Locality::class); }
    public function recording(): HasOne { return $this->hasOne(Recording::class); }
    public function validations(): HasMany { return $this->hasMany(Validation::class); }
}
