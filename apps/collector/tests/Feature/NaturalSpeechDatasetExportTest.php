<?php

namespace Tests\Feature;

use App\Enums\ContributionStatus;
use App\Enums\PromptType;
use App\Models\Category;
use App\Models\ConsentVersion;
use App\Models\Contribution;
use App\Models\ContributionSegment;
use App\Models\ContributorConsent;
use App\Models\ContributorProfile;
use App\Models\Prompt;
use App\Models\User;
use App\Models\Validation;
use App\Models\Variety;
use App\Services\DatasetExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NaturalSpeechDatasetExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_natural_segments_are_exported_separately_and_keep_one_source_split(): void
    {
        $category = Category::query()->create([
            'name' => 'Récits',
            'slug' => 'recits',
            'is_active' => true,
        ]);

        $prompt = Prompt::query()->create([
            'code' => 'NAR-EXP-001',
            'category_id' => $category->id,
            'french_text' => 'Racontez une histoire en San.',
            'context' => 'Déclencheur uniquement',
            'type' => PromptType::NARRATIVE->value,
            'difficulty' => 3,
            'priority' => 10,
            'target_contributions' => 3,
            'is_active' => true,
        ]);

        $profile = ContributorProfile::query()->create(['public_code' => 'SAN-NAT-EXPORT']);
        $variety = Variety::query()->create([
            'name' => 'San naturel test',
            'iso_code' => 'nex',
            'is_active' => true,
        ]);

        $contribution = Contribution::query()->create([
            'prompt_id' => $prompt->id,
            'contributor_profile_id' => $profile->id,
            'san_text' => 'Récit San complet.',
            'status' => ContributionStatus::APPROVED->value,
            'submitted_at' => now(),
        ]);

        Validation::query()->create([
            'contribution_id' => $contribution->id,
            'validator_id' => User::factory()->create()->id,
            'decision' => 'approve',
            'variety_id' => $variety->id,
        ]);

        $consentVersion = ConsentVersion::query()->create([
            'version' => 'natural-export-test',
            'content' => 'Consentement test',
            'allow_training' => true,
            'is_active' => true,
            'published_at' => now(),
        ]);

        ContributorConsent::query()->create([
            'contributor_profile_id' => $profile->id,
            'consent_version_id' => $consentVersion->id,
            'accepted_at' => now(),
        ]);

        foreach ([
            ['San phrase une', 'Phrase française une'],
            ['San phrase deux', 'Phrase française deux'],
        ] as $index => [$san, $french]) {
            ContributionSegment::query()->create([
                'contribution_id' => $contribution->id,
                'position' => $index + 1,
                'san_text' => $san,
                'french_translation' => $french,
                'variety_id' => $variety->id,
            ]);
        }

        $service = app(DatasetExportService::class);
        $summary = $service->summary();

        $this->assertSame(0, $summary['translationEligible']);
        $this->assertSame(2, $summary['naturalSpeechSegmentsEligible']);

        ob_start();
        $service->naturalSpeechCsv()->sendContent();
        $csv = (string) ob_get_clean();
        $lines = array_values(array_filter(preg_split('/\r\n|\n|\r/', trim($csv))));

        $this->assertCount(3, $lines);
        $header = str_getcsv($lines[0]);
        $first = array_combine($header, str_getcsv($lines[1]));
        $second = array_combine($header, str_getcsv($lines[2]));

        $this->assertSame('san-fr', $first['direction']);
        $this->assertSame($first['source_id'], $second['source_id']);
        $this->assertSame($first['split'], $second['split']);
        $this->assertSame('Racontez une histoire en San.', $first['elicitation_prompt']);
        $this->assertSame('San phrase une', $first['san']);
        $this->assertSame('Phrase française une', $first['french']);
    }
}
