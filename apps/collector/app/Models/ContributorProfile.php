<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContributorProfile extends Model
{
    protected $fillable = [
        'public_code',
        'user_id',
        'guest_token_hash',
        'locality_id',
        'locality_other',
        'fluency_level',
        'can_write_san',
        'last_seen_at',
    ];

    protected $hidden = ['guest_token_hash'];

    protected function casts(): array
    {
        return [
            'can_write_san' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function locality(): BelongsTo { return $this->belongsTo(Locality::class); }
    public function collectionSessions(): HasMany { return $this->hasMany(CollectionSession::class); }
    public function contributions(): HasMany { return $this->hasMany(Contribution::class); }
    public function consents(): HasMany { return $this->hasMany(ContributorConsent::class); }

    public function isAnonymous(): bool { return $this->user_id === null; }
}
