<?php

namespace Tests\Feature;

use App\Enums\PromptType;
use App\Models\Locality;
use App\Models\Prompt;
use App\Models\Variety;
use Database\Seeders\CategorySeeder;
use Database\Seeders\LinguisticReferenceSeeder;
use Database\Seeders\NaturalSpeechPromptSeeder;
use Database\Seeders\PromptSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_translation_catalog_contains_exactly_500_prompts_plus_natural_speech_topics(): void
    {
        $this->seed(CategorySeeder::class);
        $this->seed(PromptSeeder::class);
        $this->seed(NaturalSpeechPromptSeeder::class);

        $this->assertSame(500, Prompt::query()->whereIn('type', [PromptType::WORD->value, PromptType::SENTENCE->value])->count());
        $this->assertSame(350, Prompt::query()->where('type', PromptType::WORD->value)->count());
        $this->assertSame(150, Prompt::query()->where('type', PromptType::SENTENCE->value)->count());
        $this->assertGreaterThanOrEqual(30, Prompt::query()->where('type', PromptType::NARRATIVE->value)->count());
    }

    public function test_linguistic_reference_keeps_locality_as_documented_suggestion_not_validated_variety(): void
    {
        $this->seed(LinguisticReferenceSeeder::class);

        $this->assertDatabaseHas('varieties', ['name' => 'San Maka', 'iso_code' => 'sbd']);
        $this->assertDatabaseHas('varieties', ['name' => 'San Matya', 'iso_code' => 'stj']);
        $this->assertDatabaseHas('varieties', ['name' => 'San Maya', 'iso_code' => 'sym']);

        $toma = Locality::query()->where('name', 'Toma')->with('suggestedVariety')->firstOrFail();
        $tougan = Locality::query()->where('name', 'Tougan')->with('suggestedVariety')->firstOrFail();

        $this->assertSame('San Maka', $toma->suggestedVariety->name);
        $this->assertSame('research_supported', $toma->suggested_variety_status);
        $this->assertNotEmpty($toma->suggested_variety_source);

        $this->assertSame('San Matya', $tougan->suggestedVariety->name);
        $this->assertSame('research_supported', $tougan->suggested_variety_status);
        $this->assertNotEmpty($tougan->suggested_variety_source);

        $this->assertSame('stj', Variety::query()->where('name', 'San Matya')->value('iso_code'));
    }
}
