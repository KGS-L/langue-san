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
                ['version' => '1.1'],
                [
                    'content' => 'J’accepte que mes réponses écrites et/ou audio soient conservées par le projet Langue SAN, transcrites et examinées par des personnes autorisées, puis utilisées lorsqu’elles sont validées pour documenter la langue et construire ou évaluer de futurs outils de traduction et d’apprentissage. Les enregistrements audio restent privés et ne sont jamais publiés publiquement sans autorisation spécifique. Les audios de contributions définitivement rejetées sont supprimés après 90 jours ; les audios encore en attente sans traitement depuis 12 mois sont supprimés automatiquement. Je peux depuis mon compte demander une correction, supprimer un audio ou retirer une contribution. Le retrait d’une contribution supprime immédiatement son contenu linguistique encore stocké et son audio, et l’exclut des futurs exports. Les demandes d’anonymisation ou autres demandes nécessitant une intervention humaine ont un objectif de traitement sous 30 jours.',
                    'allow_training' => true,
                    'allow_audio_publication' => false,
                    'is_active' => true,
                    'published_at' => now(),
                ],
            );
        });
    }
}
