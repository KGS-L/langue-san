<?php

namespace App\Enums;

enum DataRequestType: string
{
    case CORRECTION = 'correction';
    case WITHDRAW_CONTRIBUTION = 'withdraw_contribution';
    case DELETE_AUDIO = 'delete_audio';
    case ANONYMIZE_ACCOUNT = 'anonymize_account';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CORRECTION => 'Demander une correction',
            self::WITHDRAW_CONTRIBUTION => 'Retirer une contribution',
            self::DELETE_AUDIO => 'Supprimer un enregistrement audio',
            self::ANONYMIZE_ACCOUNT => 'Anonymiser / supprimer mes données de compte',
            self::OTHER => 'Autre demande concernant mes données',
        };
    }

    public function requiresContribution(): bool
    {
        return in_array($this, [
            self::CORRECTION,
            self::WITHDRAW_CONTRIBUTION,
            self::DELETE_AUDIO,
        ], true);
    }
}
