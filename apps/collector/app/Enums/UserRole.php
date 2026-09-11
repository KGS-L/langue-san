<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case CONTRIBUTOR = 'contributor';
    case TRANSCRIBER = 'transcriber';
    case VALIDATOR = 'validator';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrateur',
            self::MODERATOR => 'Modérateur',
            self::CONTRIBUTOR => 'Contributeur',
            self::TRANSCRIBER => 'Transcripteur',
            self::VALIDATOR => 'Validateur linguistique',
        };
    }
}
