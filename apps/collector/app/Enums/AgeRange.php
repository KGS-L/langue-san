<?php

namespace App\Enums;

enum AgeRange: string
{
    case AGE_18_24 = '18-24';
    case AGE_25_34 = '25-34';
    case AGE_35_44 = '35-44';
    case AGE_45_54 = '45-54';
    case AGE_55_64 = '55-64';
    case AGE_65_PLUS = '65+';

    public function label(): string
    {
        return $this->value === '65+' ? '65 ans et plus' : $this->value.' ans';
    }
}
