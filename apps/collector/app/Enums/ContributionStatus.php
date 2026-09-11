<?php

namespace App\Enums;

enum ContributionStatus: string
{
    case PENDING = 'pending';
    case TRANSCRIBED = 'transcribed';
    case VALIDATED_ONCE = 'validated_once';
    case VALIDATED_TWICE = 'validated_twice';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'À transcrire',
            self::TRANSCRIBED => 'À valider',
            self::VALIDATED_ONCE => '1re validation',
            self::VALIDATED_TWICE => 'À départager',
            self::APPROVED => 'Approuvée',
            self::REJECTED => 'Rejetée',
        };
    }

    public function publicLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Reçue',
            self::TRANSCRIBED, self::VALIDATED_ONCE, self::VALIDATED_TWICE => 'En vérification',
            self::APPROVED => 'Validée',
            self::REJECTED => 'Non retenue',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning text-dark',
            self::TRANSCRIBED => 'bg-info text-dark',
            self::VALIDATED_ONCE => 'bg-primary',
            self::VALIDATED_TWICE => 'bg-danger',
            self::APPROVED => 'bg-success',
            self::REJECTED => 'bg-secondary',
        };
    }

    public function publicBadgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'text-bg-light border',
            self::TRANSCRIBED, self::VALIDATED_ONCE, self::VALIDATED_TWICE => 'text-bg-warning',
            self::APPROVED => 'text-bg-success',
            self::REJECTED => 'text-bg-secondary',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED], true);
    }

    public function canBeTranscribed(): bool
    {
        return in_array($this, [self::PENDING, self::TRANSCRIBED], true);
    }

    public function canBeValidated(): bool
    {
        return in_array($this, [self::TRANSCRIBED, self::VALIDATED_ONCE, self::VALIDATED_TWICE], true);
    }
}
