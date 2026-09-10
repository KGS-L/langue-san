<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'status'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    public function profile(): HasOne { return $this->hasOne(ContributorProfile::class); }
    public function contributions(): HasMany { return $this->hasMany(Contribution::class); }
    public function validations(): HasMany { return $this->hasMany(Validation::class, 'validator_id'); }
    public function collectionSessions(): HasMany { return $this->hasMany(CollectionSession::class); }
    public function consents(): HasMany { return $this->hasMany(UserConsent::class); }

    public function isAdmin(): bool { return $this->role === UserRole::ADMIN; }
    public function isModerator(): bool { return $this->role === UserRole::MODERATOR; }
    public function isContributor(): bool { return $this->role === UserRole::CONTRIBUTOR; }
    public function isStaff(): bool { return $this->isAdmin() || $this->isModerator(); }
}
