<?php

namespace Tests\Feature;

use App\Enums\ContributionStatus;
use App\Models\Category;
use App\Models\ConsentVersion;
use App\Models\Contribution;
use App\Models\ContributorConsent;
use App\Models\ContributorProfile;
use App\Models\Prompt;
use App\Models\Recording;
use App\Models\User;
use App\Models\Validation;
use App\Models\Variety;
use App\Services\DataRequestService;
use App\Services\DataRetentionService;
use App\Services\DatasetExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DataPrivacyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_withdrawal_deletes_linguistic_content_and_audio_and_excludes_export(): void
    {
        Storage::fake('local');
        [$profile, $contribution] = $this->eligibleContribution();

        Storage::disk('local')->put('recordings/test.webm', 'audio');
        Recording::query()->create([
            'contribution_id' => $contribution->id,
            'disk' => 'local',
            'path' => 'recordings/test.webm',
            'mime_type' => 'audio/webm',
        ]);

        $request = app(DataRequestService::class)->submit($profile, [
            'type' => 'withdraw_contribution',
            'contribution_id' => $contribution->id,
            'details' => null,
        ]);

        $fresh = $contribution->fresh();
        $this->assertNotNull($fresh->withdrawn_at);
        $this->assertNull($fresh->san_text);
        $this->assertNull($fresh->submitted_san_text);
        $this->assertDatabaseMissing('recordings', ['contribution_id' => $contribution->id]);
        $this->assertDatabaseMissing('validations', ['contribution_id' => $contribution->id]);
        Storage::disk('local')->assertMissing('recordings/test.webm');
        $this->assertSame('completed', $request->status->value);
        $this->assertSame(0, app(DatasetExportService::class)->summary()['eligible']);
    }

    public function test_contributor_cannot_manage_someone_elses_contribution(): void
    {
        [$profile, $contribution] = $this->eligibleContribution();
        $otherProfile = ContributorProfile::query()->create(['public_code' => 'SAN-OTHER-001']);

        $this->expectException(ValidationException::class);

        app(DataRequestService::class)->submit($otherProfile, [
            'type' => 'delete_audio',
            'contribution_id' => $contribution->id,
            'details' => null,
        ]);
    }

    public function test_rejected_audio_is_purged_after_retention_period(): void
    {
        Storage::fake('local');
        [$profile, $approved] = $this->eligibleContribution();

        $rejected = Contribution::query()->create([
            'prompt_id' => $approved->prompt_id,
            'contributor_profile_id' => $profile->id,
            'san_text' => 'ancienne réponse',
            'status' => ContributionStatus::REJECTED->value,
            'submitted_at' => now()->subDays(100),
        ]);
        $rejected->forceFill(['updated_at' => now()->subDays(100)])->saveQuietly();

        Storage::disk('local')->put('recordings/rejected.webm', 'audio');
        Recording::query()->create([
            'contribution_id' => $rejected->id,
            'disk' => 'local',
            'path' => 'recordings/rejected.webm',
            'mime_type' => 'audio/webm',
        ]);

        $result = app(DataRetentionService::class)->purgeExpiredAudio();

        $this->assertSame(1, $result['rejected']);
        $this->assertDatabaseMissing('recordings', ['contribution_id' => $rejected->id]);
        Storage::disk('local')->assertMissing('recordings/rejected.webm');
    }

    private function eligibleContribution(): array
    {
        $category = Category::query()->create([
            'name' => 'Confidentialité test',
            'slug' => 'privacy-test',
            'is_active' => true,
        ]);

        $prompt = Prompt::query()->create([
            'code' => 'PRIV-001',
            'category_id' => $category->id,
            'french_text' => 'Bonjour',
            'type' => 'word',
            'difficulty' => 1,
            'priority' => 10,
            'target_contributions' => 3,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $profile = ContributorProfile::query()->create([
            'public_code' => 'SAN-PRIV-001',
            'user_id' => $user->id,
        ]);

        $contribution = Contribution::query()->create([
            'prompt_id' => $prompt->id,
            'contributor_profile_id' => $profile->id,
            'san_text' => 'Fo Gouni',
            'submitted_san_text' => 'Fo Gouni',
            'status' => ContributionStatus::APPROVED->value,
            'submitted_at' => now(),
        ]);

        $variety = Variety::query()->create([
            'name' => 'Variété test',
            'iso_code' => 'tst',
            'is_active' => true,
        ]);

        Validation::query()->create([
            'contribution_id' => $contribution->id,
            'validator_id' => User::factory()->create()->id,
            'decision' => 'approve',
            'variety_id' => $variety->id,
        ]);

        $version = ConsentVersion::query()->create([
            'version' => 'privacy-test',
            'content' => 'Consentement test',
            'allow_training' => true,
            'is_active' => true,
            'published_at' => now(),
        ]);

        ContributorConsent::query()->create([
            'contributor_profile_id' => $profile->id,
            'consent_version_id' => $version->id,
            'accepted_at' => now(),
        ]);

        return [$profile, $contribution];
    }
}
