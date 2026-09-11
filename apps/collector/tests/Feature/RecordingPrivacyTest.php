<?php

namespace Tests\Feature;

use App\Enums\ContributionStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Contribution;
use App\Models\ContributorProfile;
use App\Models\Prompt;
use App\Models\Recording;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecordingPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
    }

    public function test_plain_contributor_cannot_stream_private_recording(): void
    {
        $recording = $this->makeRecording();
        $user = User::factory()->create();
        $user->assignRole(UserRole::CONTRIBUTOR->value);

        $this->actingAs($user)
            ->get(route('admin.recordings.show', $recording))
            ->assertForbidden();
    }

    public function test_transcriber_can_stream_private_recording_through_authorized_route(): void
    {
        $recording = $this->makeRecording();
        $user = User::factory()->create();
        $user->assignRole(UserRole::CONTRIBUTOR->value, UserRole::TRANSCRIBER->value);

        $this->actingAs($user)
            ->get(route('admin.recordings.show', $recording))
            ->assertOk()
            ->assertHeader('content-type', 'audio/webm');
    }

    private function makeRecording(): Recording
    {
        $category = Category::query()->create([
            'name' => 'Audio test',
            'slug' => 'audio-test',
            'is_active' => true,
        ]);

        $prompt = Prompt::query()->create([
            'code' => 'AUD-001',
            'category_id' => $category->id,
            'french_text' => 'Test audio',
            'type' => 'sentence',
            'difficulty' => 1,
            'priority' => 10,
            'target_contributions' => 3,
            'is_active' => true,
        ]);

        $profile = ContributorProfile::query()->create([
            'public_code' => 'SAN-AUDIO-001',
        ]);

        $contribution = Contribution::query()->create([
            'prompt_id' => $prompt->id,
            'contributor_profile_id' => $profile->id,
            'status' => ContributionStatus::PENDING->value,
            'submitted_at' => now(),
        ]);

        $path = 'recordings/SAN-AUDIO-001/test.webm';
        Storage::disk('local')->put($path, 'fake-audio-content');

        return Recording::query()->create([
            'contribution_id' => $contribution->id,
            'disk' => 'local',
            'path' => $path,
            'mime_type' => 'audio/webm',
            'size_bytes' => 18,
            'duration_ms' => 1000,
            'quality_status' => 'pending',
        ]);
    }
}
