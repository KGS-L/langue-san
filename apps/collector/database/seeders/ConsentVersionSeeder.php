<?php

namespace Database\Seeders;

use App\Models\ConsentVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsentVersionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            ConsentVersion::query()->update(['is_active' => false]);

            ConsentVersion::query()->updateOrCreate(
                ['version' => '1.0'],
                [
                    'content' => 'J’accepte que mes réponses écrites et/ou audio soient conservées par le projet Langue SAN, transcrites et examinées par des validateurs, puis utilisées lorsqu’elles sont validées pour documenter la langue et construire ou évaluer de futurs outils de traduction et d’apprentissage. Les enregistrements audio restent privés et ne sont pas publiés publiquement sans autorisation spécifique.',
                    'allow_training' => true,
                    'allow_audio_publication' => false,
                    'is_active' => true,
                    'published_at' => now(),
                ],
            );
        });
    }
}
