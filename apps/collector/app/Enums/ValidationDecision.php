<?php

namespace App\Enums;

enum ValidationDecision: string
{
    case APPROVE = 'approve';
    case CORRECT = 'correct';
    case REJECT = 'reject';

    public function label(): string
    {
        return match ($this) {
            self::APPROVE => 'Approuver',
            self::CORRECT => 'Corriger',
            self::REJECT => 'Rejeter',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::APPROVE => 'bg-success',
            self::CORRECT => 'bg-warning text-dark',
            self::REJECT => 'bg-danger',
        };
    }
}
