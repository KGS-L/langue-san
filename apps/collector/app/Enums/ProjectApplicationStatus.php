<?php

namespace App\Enums;

enum ProjectApplicationStatus: string
{
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::UNDER_REVIEW => 'En cours d’examen',
            self::APPROVED => 'Acceptée',
            self::REJECTED => 'Refusée',
            self::WITHDRAWN => 'Retirée',
        };
    }
}
