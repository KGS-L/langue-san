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
                    'description' => 'Variété linguistique de référence du projet. Cette information est utilisée côté administration et ne doit pas être imposée comme choix technique au contributeur.',
                    'is_active' => true,
                ],
            );

            $matya = Variety::updateOrCreate(
                ['iso_code' => 'sym'],
                [
                    'name' => 'San Matya',
                    'description' => 'Variété linguistique de référence du projet. Cette information est utilisée côté administration et ne doit pas être imposée comme choix technique au contributeur.',
                    'is_active' => true,
                ],
            );

            Variety::updateOrCreate(
                ['iso_code' => 'stj'],
                [
                    'name' => 'San Maya',
                    'description' => 'Variété linguistique de référence du projet. Aucune localité par défaut n’est associée tant que la correspondance n’a pas été confirmée dans le référentiel.',
                    'is_active' => true,
                ],
            );

            Locality::updateOrCreate(
                ['name' => 'Toma', 'province' => 'Nayala'],
                [
                    'suggested_variety_id' => $maka->id,
                    'notes' => 'Correspondance proposée pour orienter la collecte. La variété finale d’une contribution reste une donnée de validation linguistique.',
                    'is_active' => true,
                ],
            );

            Locality::updateOrCreate(
                ['name' => 'Tougan', 'province' => 'Sourou'],
                [
                    'suggested_variety_id' => $matya->id,
                    'notes' => 'Correspondance proposée pour orienter la collecte. La variété finale d’une contribution reste une donnée de validation linguistique.',
                    'is_active' => true,
                ],
            );
        });
    }
}
