<?php

namespace Tests\Feature;

use App\Enums\ContributionStatus;
use App\Enums\PromptType;
use App\Models\Category;
use App\Models\Contribution;
use App\Models\ContributionSegment;
use App\Models\ContributorProfile;
use App\Models\Prompt;
use App\Models\User;
use App\Models\Variety;
use App\Services\ValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class NaturalSpeechWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_natural_speech_cannot_be_validated_before_segmentation_and_french_translation(): void
    {
        [$contribution, $variety] = $this->makeNarrativeContribution();
        $validator = User::factory()->create();
        $service = app(ValidationService::class);

        try {
            $service->validate($contribution, $validator, [
                'decision' => 'approve',
                'variety_id' => $variety->id,
                'san_text_corrected' => null,
                'notes' => null,
            ]);
            $this->fail('Validation should have been blocked before segmentation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('decision', $exception->errors());
        }

        ContributionSegment::query()->create([
            'contribution_id' => $contribution->id,
            'position' => 1,
            'san_text' => 'Segment San naturel',
            'french_translation' => 'Traduction française du segment',
            'variety_id' => $variety->id,
        ]);

        $service->validate($contribution->fresh(), $validator, [
            'decision' => 'approve',
            'variety_id' => $variety->id,
            'san_text_corrected' => null,
            'notes' => null,
        ]);

        $this->assertSame(ContributionStatus::VALIDATED_ONCE, $contribution->fresh()->status);
    }

    private function makeNarrativeContribution(): array
    {
        $category = Category::query()->create([
            'name' => 'Traditions',
            'slug' => 'traditions',
            'is_active' => true,
        ]);

        $prompt = Prompt::query()->create([
            'code' => 'NAR-TEST-001',
            'category_id' => $category->id,
            'french_text' => 'Racontez un mariage traditionnel.',
            'context' => 'Parole naturelle',
            'type' => PromptType::NARRATIVE->value,
            'difficulty' => 3,
            'priority' => 10,
            'target_contributions' => 3,
            'is_active' => true,
        ]);

        $profile = ContributorProfile::query()->create([
            'public_code' => 'SAN-NARRATIVE-001',
        ]);

        $contribution = Contribution::query()->create([
            'prompt_id' => $prompt->id,
            'contributor_profile_id' => $profile->id,
            'san_text' => 'Une transcription complète en San.',
            'status' => ContributionStatus::TRANSCRIBED->value,
            'submitted_at' => now(),
        ]);

        $variety = Variety::query()->create([
            'name' => 'San test',
            'iso_code' => 'nst',
            'is_active' => true,
        ]);

        return [$contribution, $variety];
    }
}
