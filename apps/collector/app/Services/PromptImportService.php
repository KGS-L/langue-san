<?php

namespace App\Services;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\PromptRepositoryInterface;
use App\Enums\PromptType;
use RuntimeException;
use SplFileObject;

class PromptImportService
{
    private const REQUIRED_HEADERS = ['code', 'category', 'type', 'french_text'];

    public function __construct(
        private readonly PromptRepositoryInterface $prompts,
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function import(string $path): array
    {
        $file = new SplFileObject($path, 'r');
        $firstLine = (string) $file->fgets();
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        $file->rewind();
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $file->setCsvControl($delimiter);

        $headers = $file->fgetcsv();
        if (! is_array($headers)) {
            throw new RuntimeException('Le fichier CSV est vide.');
        }

        $headers = array_map(function ($header) {
            return strtolower(trim((string) $header, "\xEF\xBB\xBF \t\n\r\0\x0B"));
        }, $headers);

        foreach (self::REQUIRED_HEADERS as $required) {
            if (! in_array($required, $headers, true)) {
                throw new RuntimeException("Colonne obligatoire absente : {$required}");
            }
        }

        $createdOrUpdated = 0;
        $errors = [];
        $line = 1;

        foreach ($file as $row) {
            $line++;

            if (! is_array($row) || $row === [null] || count(array_filter($row, fn ($value) => $value !== null && trim((string) $value) !== '')) === 0) {
                continue;
            }

            if (count($row) !== count($headers)) {
                $errors[] = "Ligne {$line} : nombre de colonnes invalide.";
                continue;
            }

            $data = array_combine($headers, $row);
            if ($data === false) {
                $errors[] = "Ligne {$line} : impossible de lire les colonnes.";
                continue;
            }

            $code = strtoupper(trim((string) ($data['code'] ?? '')));
            $categoryValue = trim((string) ($data['category'] ?? ''));
            $type = PromptType::tryFrom(strtolower(trim((string) ($data['type'] ?? ''))));
            $frenchText = trim((string) ($data['french_text'] ?? ''));

            if ($code === '' || ! preg_match('/^[A-Z0-9-]+$/', $code)) {
                $errors[] = "Ligne {$line} : code invalide.";
                continue;
            }

            $category = $this->categories->findBySlugOrName($categoryValue);
            if (! $category) {
                $errors[] = "Ligne {$line} : catégorie '{$categoryValue}' introuvable.";
                continue;
            }

            if (! $type) {
                $errors[] = "Ligne {$line} : type invalide, utilisez 'word' ou 'sentence'.";
                continue;
            }

            if ($frenchText === '') {
                $errors[] = "Ligne {$line} : french_text est obligatoire.";
                continue;
            }

            $difficulty = (int) ($data['difficulty'] ?? 1);
            $priority = (int) ($data['priority'] ?? 10);
            $target = (int) ($data['target_contributions'] ?? 3);

            if ($difficulty < 1 || $difficulty > 5 || $priority < 0 || $priority > 100 || $target < 1 || $target > 100) {
                $errors[] = "Ligne {$line} : difficulté, priorité ou cible hors limites.";
                continue;
            }

            $isActiveRaw = strtolower(trim((string) ($data['is_active'] ?? '1')));
            $isActive = ! in_array($isActiveRaw, ['0', 'false', 'non', 'no'], true);

            $this->prompts->upsertByCode([
                'code' => $code,
                'category_id' => $category->id,
                'type' => $type->value,
                'french_text' => $frenchText,
                'context' => blank($data['context'] ?? null) ? null : trim((string) $data['context']),
                'difficulty' => $difficulty,
                'priority' => $priority,
                'target_contributions' => $target,
                'is_active' => $isActive,
            ]);

            $createdOrUpdated++;
        }

        return [
            'processed' => $createdOrUpdated,
            'errors' => $errors,
        ];
    }
}
