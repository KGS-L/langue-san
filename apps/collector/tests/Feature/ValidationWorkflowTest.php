<?php

namespace Tests\Feature;

use App\Enums\ContributionStatus;
use App\Models\Category;
use App\Models\Contribution;
use App\Models\ContributorProfile;
use App\Models\Prompt;
use App\Models\User;
use App\Models\Variety;
use App\Services\ValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ValidationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_independent_matching_approvals_mark_contribution_as_approved(): void
    {
        [$contribution, $variety] = $this->makeTranscribedContribution();
        $firstValidator = User::factory()->create();
        $secondValidator = User::factory()->create();
        $service = app(ValidationService::class);

        $service->validate($contribution, $firstValidator, [
            'decision' => 'approve',
            'variety_id' => $variety->id,
            'san_text_corrected' => null,
            'notes' => null,
        ]);

        $this->assertSame(ContributionStatus::VALIDATED_ONCE, $contribution->fresh()->status);

        $service->validate($contribution->fresh(), $secondValidator, [
            'decision' => 'approve',
            'variety_id' => $variety->id,
            'san_text_corrected' => null,
            'notes' => null,
        ]);

        $this->assertSame(ContributionStatus::APPROVED, $contribution->fresh()->status);
        $this->assertCount(2, $contribution->fresh()->validations);
    }

    public function test_same_validator_cannot_validate_twice(): void
    {
        [$contribution, $variety] = $this->makeTranscribedContribution();
        $validator = User::factory()->create();
        $service = app(ValidationService::class);

        $data = [
            'decision' => 'approve',
            'variety_id' => $variety->id,
            'san_text_corrected' => null,
            'notes' => null,
        ];

        $service->validate($contribution, $validator, $data);

        $this->expectException(ValidationException::class);
        $service->validate($contribution->fresh(), $validator, $data);
    }

    public function test_two_independent_rejections_mark_contribution_as_rejected(): void
    {
        [$contribution] = $this->makeTranscribedContribution();
        $service = app(ValidationService::class);

        foreach ([User::factory()->create(), User::factory()->create()] as $validator) {
            $service->validate($contribution->fresh(), $validator, [
                'decision' => 'reject',
                'variety_id' => null,
                'san_text_corrected' => null,
                'notes' => 'Réponse non exploitable.',
            ]);
        }

        $this->assertSame(ContributionStatus::REJECTED, $contribution->fresh()->status);
    }

    private function makeTranscribedContribution(): array
    {
        $category = Category::query()->create([
            'name' => 'Test',
            'slug' => 'test',
            'is_active' => true,
        ]);

        $prompt = Prompt::query()->create([
            'code' => 'TEST-001',
            'category_id' => $category->id,
            'french_text' => 'Bonjour',
            'type' => 'word',
            'difficulty' => 1,
            'priority' => 10,
            'target_contributions' => 3,
            'is_active' => true,
        ]);

        $profile = ContributorProfile::query()->create([
            'public_code' => 'SAN-TEST-001',
        ]);

        $contribution = Contribution::query()->create([
            'prompt_id' => $prompt->id,
            'contributor_profile_id' => $profile->id,
            'san_text' => 'Fo Gouni',
            'status' => ContributionStatus::TRANSCRIBED->value,
            'submitted_at' => now(),
        ]);

        $variety = Variety::query()->create([
            'name' => 'Variété test',
            'iso_code' => 'tst',
            'is_active' => true,
        ]);

        return [$contribution, $variety];
    }
}
