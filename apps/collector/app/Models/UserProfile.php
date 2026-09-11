<?php

namespace App\Models;

use App\Enums\AgeRange;
use App\Enums\ProfessionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'country_code',
        'age_range',
        'profession',
        'profession_other',
        'organization',
        'avatar_path',
        'public_profile_enabled',
        'public_display_name',
        'public_bio',
        'github_url',
        'linkedin_url',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'age_range' => AgeRange::class,
            'profession' => ProfessionType::class,
            'public_profile_enabled' => 'boolean',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isComplete(): bool
    {
        return $this->onboarding_completed_at !== null;
    }

    public function professionLabel(): string
    {
        if ($this->profession === ProfessionType::OTHER) {
            return $this->profession_other ?: 'Autre';
        }

        return $this->profession?->label() ?? 'Non renseignée';
    }
}
