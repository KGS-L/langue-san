<?php

namespace App\Models;

use App\Enums\DataRequestStatus;
use App\Enums\DataRequestType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataRequest extends Model
{
    protected $fillable = [
        'contributor_profile_id',
        'contribution_id',
        'type',
        'status',
        'details',
        'resolution_notes',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => DataRequestType::class,
            'status' => DataRequestStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    public function contributorProfile(): BelongsTo { return $this->belongsTo(ContributorProfile::class); }
    public function contribution(): BelongsTo { return $this->belongsTo(Contribution::class); }
    public function processor(): BelongsTo { return $this->belongsTo(User::class, 'processed_by'); }
}
