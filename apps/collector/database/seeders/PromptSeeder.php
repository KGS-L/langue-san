<?php

namespace Database\Seeders;

use App\Enums\PromptType;
use App\Models\Category;
use App\Models\Prompt;
use Illuminate\Database\Seeder;
use RuntimeException;

class PromptSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'salutations' => [
                'prefix' => 'SAL',
                'words' => [
                    ['Bonjour', 'Salutation générale'],
                    ['Bonsoir', 'Salutation utilisée le soir'],
                    ['Merci', null],
                    ["S'il vous plaît", 'Formule de politesse'],
                    ['Au revoir', null],
                    ['Bienvenue', null],
                    ['Pardon', 'Demander pardon ou attirer poliment l’attention'],
                ],
                'sentences' => [
                    ['Comment vas-tu ?', null],
                    ['Je vais bien.', null],
                    ['À demain.', null],
                ],
            ],
            'presentation-identite' => [
                'prefix' => 'PRE',
                'words' => [
                    ['Nom', 'Nom d’une personne'],
                    ['Prénom', null],
                    ['Homme', 'Personne adulte de sexe masculin'],
                    ['Femme', 'Personne adulte de sexe féminin'],
                    ['Enfant', 'Jeune personne'],
                    ['Village', null],
                    ['Âge', 'Nombre d’années vécues'],
                ],
                'sentences' => [
                    ["Comment t'appelles-tu ?", null],
                    ["Je m'appelle …", null],
                    ['Où habites-tu ?', null],
                ],
            ],
            'famille' => [
                'prefix' => 'FAM',
                'words' => [
                    ['Père', null],
                    ['Mère', null],
                    ['Frère', null],
                    ['Sœur', null],
                    ['Enfant', 'Fils ou fille'],
                    ['Mari', 'Époux'],
                    ['Femme', 'Épouse'],
                ],
                'sentences' => [
                    ['Voici mon père.', null],
                    ['Ma mère est à la maison.', null],
                    ["J'ai deux enfants.", null],
                ],
            ],
            'nombres' => [
                'prefix' => 'NUM',
                'words' => [
                    ['Un', 'Nombre 1'],
                    ['Deux', 'Nombre 2'],
                    ['Trois', 'Nombre 3'],
                    ['Cinq', 'Nombre 5'],
                    ['Dix', 'Nombre 10'],
                    ['Cent', 'Nombre 100'],
                    ['Mille', 'Nombre 1000'],
                ],
                'sentences' => [
                    ['Nous sommes trois.', null],
                    ["J'en veux deux.", null],
                    ['Il y a cinq personnes.', null],
                ],
            ],
            'temps-jours' => [
                'prefix' => 'TEM',
                'words' => [
                    ["Aujourd'hui", null],
                    ['Demain', null],
                    ['Hier', null],
                    ['Matin', null],
                    ['Soir', null],
                    ['Jour', null],
                    ['Nuit', null],
                ],
                'sentences' => [
                    ["Je viens demain.", null],
                    ["Il est parti ce matin.", null],
                    ["Nous travaillons aujourd'hui.", null],
                ],
            ],
            'nourriture' => [
                'prefix' => 'NOU',
                'words' => [
                    ['Eau', null],
                    ['Riz', null],
                    ['Mil', 'Céréale alimentaire'],
                    ['Viande', null],
                    ['Sauce', 'Sauce alimentaire'],
                    ['Manger', 'Verbe'],
                    ['Boire', 'Verbe'],
                ],
                'sentences' => [
                    ["Je veux boire de l'eau.", null],
                    ['Nous allons manger.', null],
                    ['La nourriture est prête.', null],
                ],
            ],
            'maison' => [
                'prefix' => 'MAI',
                'words' => [
                    ['Maison', 'Lieu d’habitation'],
                    ['Porte', 'Porte d’une maison'],
                    ['Cour', 'Cour de la maison'],
                    ['Chambre', 'Pièce où l’on dort'],
                    ['Feu', 'Feu utilisé pour cuisiner ou se chauffer'],
                    ['Chaise', null],
                    ['Dormir', 'Verbe'],
                ],
                'sentences' => [
                    ['Entre dans la maison.', null],
                    ['Ferme la porte.', null],
                    ['Les enfants dorment.', null],
                ],
            ],
            'marche-commerce' => [
                'prefix' => 'MAR',
                'words' => [
                    ['Marché', 'Lieu de commerce'],
                    ['Argent', 'Monnaie / argent pour payer'],
                    ['Prix', 'Montant demandé pour un produit'],
                    ['Acheter', 'Verbe'],
                    ['Vendre', 'Verbe'],
                    ['Donner', 'Verbe'],
                    ['Cher', 'Qui coûte beaucoup'],
                ],
                'sentences' => [
                    ['Combien ça coûte ?', null],
                    ['Je veux acheter ceci.', null],
                    ["C'est trop cher.", null],
                ],
            ],
            'deplacements' => [
                'prefix' => 'DEP',
                'words' => [
                    ['Aller', 'Verbe de déplacement'],
                    ['Venir', 'Verbe de déplacement'],
                    ['Partir', 'Verbe de déplacement'],
                    ['Route', 'Voie de déplacement'],
                    ['Village', 'Localité rurale'],
                    ['Vélo', 'Bicyclette'],
                    ['Marcher', 'Se déplacer à pied'],
                ],
                'sentences' => [
                    ['Où vas-tu ?', null],
                    ['Je vais au village.', null],
                    ['Viens ici.', null],
                ],
            ],
            'ecole' => [
                'prefix' => 'ECO',
                'words' => [
                    ['École', null],
                    ['Enseignant', 'Personne qui enseigne'],
                    ['Cahier', null],
                    ['Livre', null],
                    ['Écrire', 'Verbe'],
                    ['Lire', 'Verbe'],
                    ['Comprendre', 'Verbe'],
                ],
                'sentences' => [
                    ['Ouvre ton cahier.', null],
                    ['Lis cette phrase.', null],
                    ["Je n'ai pas compris.", null],
                ],
            ],
            'travail' => [
                'prefix' => 'TRA',
                'words' => [
                    ['Travail', 'Activité professionnelle ou productive'],
                    ['Champ', 'Terrain cultivé'],
                    ['Cultiver', 'Travailler la terre'],
                    ['Récolte', 'Produits récoltés ou action de récolter'],
                    ['Tailleur', 'Personne qui confectionne des vêtements'],
                    ['Travailler', 'Verbe'],
                    ['Fatigué', 'État de fatigue'],
                ],
                'sentences' => [
                    ['Je vais au travail.', null],
                    ['Nous travaillons au champ.', null],
                    ["Je suis fatigué aujourd'hui.", null],
                ],
            ],
        ];

        foreach ($catalog as $categorySlug => $group) {
            $category = Category::query()->where('slug', $categorySlug)->first();

            if (! $category) {
                throw new RuntimeException("Catégorie introuvable pour le PromptSeeder : {$categorySlug}");
            }

            foreach ($group['words'] as $index => [$text, $context]) {
                $this->upsertPrompt(
                    $category->id,
                    sprintf('%s-W-%03d', $group['prefix'], $index + 1),
                    PromptType::WORD,
                    $text,
                    $context,
                    1,
                );
            }

            foreach ($group['sentences'] as $index => [$text, $context]) {
                $this->upsertPrompt(
                    $category->id,
                    sprintf('%s-S-%03d', $group['prefix'], $index + 1),
                    PromptType::SENTENCE,
                    $text,
                    $context,
                    2,
                );
            }
        }
    }

    private function upsertPrompt(
        int $categoryId,
        string $code,
        PromptType $type,
        string $text,
        ?string $context,
        int $difficulty,
    ): void {
        Prompt::query()->updateOrCreate(
            ['code' => $code],
            [
                'category_id' => $categoryId,
                'french_text' => $text,
                'context' => $context,
                'type' => $type,
                'difficulty' => $difficulty,
                'priority' => 10,
                'target_contributions' => 3,
                'is_active' => true,
            ],
        );
    }
}
