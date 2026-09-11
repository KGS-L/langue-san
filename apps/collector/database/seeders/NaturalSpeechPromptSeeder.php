<?php

namespace Database\Seeders;

use App\Enums\PromptType;
use App\Models\Category;
use App\Models\Prompt;
use Illuminate\Database\Seeder;
use RuntimeException;

class NaturalSpeechPromptSeeder extends Seeder
{
    public function run(): void
    {
        $prompts = require database_path('data/natural_speech_prompts.php');

        if (count($prompts) < 30) {
            throw new RuntimeException('Le catalogue de parole naturelle doit contenir au moins 30 sujets.');
        }

        foreach (array_values($prompts) as $index => [$prefix, $categorySlug, $instruction]) {
            $category = Category::query()->where('slug', $categorySlug)->first();

            if (! $category) {
                throw new RuntimeException("Catégorie introuvable pour la parole naturelle : {$categorySlug}");
            }

            Prompt::query()->updateOrCreate(
                ['code' => sprintf('NAR-%s-%03d', $prefix, $index + 1)],
                [
                    'category_id' => $category->id,
                    'french_text' => $instruction,
                    'context' => 'Parole naturelle : répondre librement en San. Il ne s’agit pas de traduire la consigne mot à mot.',
                    'type' => PromptType::NARRATIVE,
                    'difficulty' => 3,
                    'priority' => 10,
                    'target_contributions' => 3,
                    'is_active' => true,
                ],
            );
        }
    }
}
