<?php

namespace App\Enums;

enum ProjectContributionArea: string
{
    case LINGUISTICS = 'linguistics';
    case SAN_VALIDATION = 'san_validation';
    case TRANSCRIPTION = 'transcription';
    case NLP_ML = 'nlp_ml';
    case SOFTWARE = 'software';
    case DEVOPS_MLOPS = 'devops_mlops';
    case UX_PRODUCT = 'ux_product';
    case COMMUNITY = 'community';
    case RESEARCH = 'research';
    case DATA_GOVERNANCE = 'data_governance';

    public function label(): string
    {
        return match ($this) {
            self::LINGUISTICS => 'Linguistique / langues',
            self::SAN_VALIDATION => 'Validation linguistique du San',
            self::TRANSCRIPTION => 'Transcription / lexicographie',
            self::NLP_ML => 'NLP / Machine Learning / Data',
            self::SOFTWARE => 'Développement logiciel',
            self::DEVOPS_MLOPS => 'DevOps / MLOps',
            self::UX_PRODUCT => 'UX / UI / Produit',
            self::COMMUNITY => 'Communication / communauté / terrain',
            self::RESEARCH => 'Recherche / documentation',
            self::DATA_GOVERNANCE => 'Éthique / gouvernance des données',
        };
    }
}
