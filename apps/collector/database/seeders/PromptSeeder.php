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
        $catalog = require database_path('data/translation_prompts.php');
        $expectedCount = 500;
        $count = 0;

        foreach ($catalog as $categorySlug => $group) {
            $category = Category::query()->where('slug', $categorySlug)->first();

            if (! $category) {
                throw new RuntimeException("Catégorie introuvable pour le PromptSeeder : {$categorySlug}");
            }

            if (count($group['words'] ?? []) !== 14 || count($group['sentences'] ?? []) !== 6) {
                throw new RuntimeException("Le thème {$categorySlug} doit contenir exactement 14 mots/expressions et 6 phrases.");
            }

            foreach ($group['words'] as $index => $item) {
                [$text, $context] = $this->normalizeItem($item);
                $this->upsertPrompt(
                    $category->id,
                    sprintf('%s-W-%03d', $group['prefix'], $index + 1),
                    PromptType::WORD,
                    $text,
                    $context,
                    1,
                );
                $count++;
            }

            foreach ($group['sentences'] as $index => $item) {
                [$text, $context] = $this->normalizeItem($item);
                $this->upsertPrompt(
                    $category->id,
                    sprintf('%s-S-%03d', $group['prefix'], $index + 1),
                    PromptType::SENTENCE,
                    $text,
                    $context,
                    2,
                );
                $count++;
            }
        }

        if ($count !== $expectedCount) {
            throw new RuntimeException("Le catalogue de traduction doit contenir exactement {$expectedCount} prompts ; {$count} trouvés.");
        }
    }

    private function normalizeItem(string|array $item): array
    {
        if (is_array($item)) {
            return [(string) ($item[0] ?? ''), $item[1] ?? null];
        }

        return [$item, null];
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
