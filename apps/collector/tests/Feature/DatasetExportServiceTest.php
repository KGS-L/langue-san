<?php

namespace Tests\Feature;

use App\Enums\ContributionStatus;
use App\Models\Category;
use App\Models\ConsentVersion;
use App\Models\Contribution;
use App\Models\ContributorConsent;
use App\Models\ContributorProfile;
use App\Models\Prompt;
use App\Models\User;
use App\Models\Validation;
use App\Models\Variety;
use App\Services\DatasetExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatasetExportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_approved_consented_validated_rows_are_eligible(): void
    {
        [$profile, $prompt, $variety] = $this->baseData();

        $eligible = Contribution::query()->create([
            'prompt_id' => $prompt->id,
            'contributor_profile_id' => $profile->id,
            'san_text' => 'Fo Gouni',
            'status' => ContributionStatus::APPROVED->value,
            'submitted_at' => now(),
        ]);

        Validation::query()->create([
            'contribution_id' => $eligible->id,
            'validator_id' => User::factory()->create()->id,
            'decision' => 'approve',
            'variety_id' => $variety->id,
        ]);

        $consentVersion = ConsentVersion::query()->create([
            'version' => 'test-1',
            'content' => 'Consentement de test',
            'allow_training' => true,
            'is_active' => true,
            'published_at' => now(),
        ]);

        ContributorConsent::query()->create([
            'contributor_profile_id' => $profile->id,
            'consent_version_id' => $consentVersion->id,
            'accepted_at' => now(),
        ]);

        $notApproved = Contribution::query()->create([
            'prompt_id' => $prompt->id,
            'contributor_profile_id' => ContributorProfile::query()->create(['public_code' => 'SAN-NO-APPROVAL'])->id,
            'san_text' => 'Test',
            'status' => ContributionStatus::TRANSCRIBED->value,
            'submitted_at' => now(),
        ]);

        $service = app(DatasetExportService::class);
        $summary = $service->summary();

        $this->assertSame(1, $summary['eligible']);
        $this->assertSame(1, array_sum($summary['splits']));
        $this->assertSame($summary, $service->summary());
        $this->assertNotSame($eligible->id, $notApproved->id);
    }

    private function baseData(): array
    {
        $category = Category::query()->create([
            'name' => 'Export test',
            'slug' => 'export-test',
            'is_active' => true,
        ]);

        $prompt = Prompt::query()->create([
            'code' => 'EXP-001',
            'category_id' => $category->id,
            'french_text' => 'Bonjour',
            'type' => 'word',
            'difficulty' => 1,
            'priority' => 10,
            'target_contributions' => 3,
            'is_active' => true,
        ]);

        $profile = ContributorProfile::query()->create([
            'public_code' => 'SAN-EXPORT-001',
        ]);

        $variety = Variety::query()->create([
            'name' => 'Variété export',
            'iso_code' => 'ex1',
            'is_active' => true,
        ]);

        return [$profile, $prompt, $variety];
    }
}
