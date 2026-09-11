<?php

namespace App\Enums;

enum ProfessionType: string
{
    case STUDENT = 'student';
    case TEACHER = 'teacher';
    case RESEARCHER = 'researcher';
    case LINGUIST = 'linguist';
    case SOFTWARE_DEVELOPER = 'software_developer';
    case DATA_AI_ML = 'data_ai_ml';
    case ENTREPRENEUR = 'entrepreneur';
    case COMMUNICATION = 'communication';
    case COMMUNITY_FIELD = 'community_field';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::STUDENT => 'Étudiant(e)',
            self::TEACHER => 'Enseignant(e)',
            self::RESEARCHER => 'Chercheur / chercheuse',
            self::LINGUIST => 'Linguiste / spécialiste des langues',
            self::SOFTWARE_DEVELOPER => 'Développeur / développeuse logiciel',
            self::DATA_AI_ML => 'Data / IA / Machine Learning',
            self::ENTREPRENEUR => 'Entrepreneur(e)',
            self::COMMUNICATION => 'Communication / média',
            self::COMMUNITY_FIELD => 'Mobilisation communautaire / terrain',
            self::OTHER => 'Autre',
        };
    }
}
