<?php

namespace Database\Seeders;

use App\Models\Locality;
use App\Models\Variety;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LinguisticReferenceSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $maka = Variety::updateOrCreate(
                ['iso_code' => 'sbd'],
                [
                    'name' => 'San Maka',
                    'description' => 'San du Sud / Southern Samo. Variété de référence interne. Le contributeur public choisit sa localité ; la variété finale reste confirmée par validation linguistique.',
                    'is_active' => true,
                ],
            );

            $matya = Variety::updateOrCreate(
                ['iso_code' => 'stj'],
                [
                    'name' => 'San Matya',
                    'description' => 'Matya Samo. Variété de référence interne. Le contributeur public choisit sa localité ; la variété finale reste confirmée par validation linguistique.',
                    'is_active' => true,
                ],
            );

            Variety::updateOrCreate(
                ['iso_code' => 'sym'],
                [
                    'name' => 'San Maya',
                    'description' => 'Maya Samo. Variété de référence interne. Aucune localité n’est convertie automatiquement en variété validée.',
                    'is_active' => true,
                ],
            );

            Locality::updateOrCreate(
                ['name' => 'Toma', 'province' => 'Nayala'],
                [
                    'suggested_variety_id' => $maka->id,
                    'suggested_variety_status' => 'research_supported',
                    'suggested_variety_source' => 'Berthelette (2001) associe Toma/Maka au Southern Samo [sbd] ; Platiel (1974) décrit le parler Samo de Toma comme Southern Samo.',
                    'notes' => 'Suggestion interne fondée sur la documentation. Elle sert à orienter les validateurs mais ne devient jamais automatiquement la variété validée d’une contribution.',
                    'is_active' => true,
                ],
            );

            Locality::updateOrCreate(
                ['name' => 'Tougan', 'province' => 'Sourou'],
                [
                    'suggested_variety_id' => $matya->id,
                    'suggested_variety_status' => 'research_supported',
                    'suggested_variety_source' => 'Les références de classification de Matya Samo [stj] utilisent notamment « Tougan » comme nom associé à cette variété.',
                    'notes' => 'Suggestion interne fondée sur la documentation. Elle sert à orienter les validateurs mais ne devient jamais automatiquement la variété validée d’une contribution.',
                    'is_active' => true,
                ],
            );
        });
    }
}
