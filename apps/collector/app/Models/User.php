<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    public function profile(): HasOne { return $this->hasOne(ContributorProfile::class); }
    public function userProfile(): HasOne { return $this->hasOne(UserProfile::class); }
    public function validations(): HasMany { return $this->hasMany(Validation::class, 'validator_id'); }
    public function projectApplications(): HasMany { return $this->hasMany(ProjectApplication::class); }
    public function projectMembership(): HasOne { return $this->hasOne(ProjectMembership::class); }

    public function contributions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Contribution::class,
            ContributorProfile::class,
            'user_id',
            'contributor_profile_id',
        );
    }

    public function collectionSessions(): HasManyThrough
    {
        return $this->hasManyThrough(
            CollectionSession::class,
            ContributorProfile::class,
            'user_id',
            'contributor_profile_id',
        );
    }

    public function consents(): HasManyThrough
    {
        return $this->hasManyThrough(
            ContributorConsent::class,
            ContributorProfile::class,
            'user_id',
            'contributor_profile_id',
        );
    }

    public function isAdmin(): bool { return $this->hasRole(UserRole::ADMIN->value); }
    public function isModerator(): bool { return $this->hasRole(UserRole::MODERATOR->value); }
    public function isContributor(): bool { return $this->hasRole(UserRole::CONTRIBUTOR->value); }
    public function isStaff(): bool { return $this->hasAnyRole([UserRole::ADMIN->value, UserRole::MODERATOR->value]); }

    public function needsContributorOnboarding(): bool
    {
        return $this->isContributor() && ! $this->userProfile?->isComplete();
    }
}
