<?php

namespace App\Enums;

enum DataRequestStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::PROCESSING => 'En traitement',
            self::COMPLETED => 'Terminée',
            self::REJECTED => 'Refusée',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'text-bg-warning',
            self::PROCESSING => 'text-bg-info',
            self::COMPLETED => 'text-bg-success',
            self::REJECTED => 'text-bg-secondary',
        };
    }
}
