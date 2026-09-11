<?php

namespace Tests\Feature;

use App\Enums\ProjectApplicationStatus;
use App\Enums\UserRole;
use App\Models\ProjectApplication;
use App\Models\ProjectMembership;
use App\Models\User;
use App\Services\ProjectApplicationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectMemberAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_grant_validator_and_transcriber_without_removing_contributor_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN->value);

        $member = User::factory()->create();
        $member->assignRole(UserRole::CONTRIBUTOR->value);

        $application = ProjectApplication::query()->create([
            'user_id' => $member->id,
            'contribution_areas' => ['linguistics'],
            'experience' => 'Expérience de test',
            'motivation' => 'Motivation de test',
            'status' => ProjectApplicationStatus::APPROVED->value,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        ProjectMembership::query()->create([
            'user_id' => $member->id,
            'approved_application_id' => $application->id,
            'contribution_areas' => ['linguistics'],
            'is_active' => true,
            'started_at' => now(),
        ]);

        $updated = app(ProjectApplicationService::class)->updateSpecializedAccess(
            $application,
            $admin,
            [UserRole::VALIDATOR->value, UserRole::TRANSCRIBER->value],
        );

        $this->assertTrue($updated->hasRole(UserRole::CONTRIBUTOR->value));
        $this->assertTrue($updated->hasRole(UserRole::VALIDATOR->value));
        $this->assertTrue($updated->hasRole(UserRole::TRANSCRIBER->value));
        $this->assertFalse($updated->hasRole(UserRole::MODERATOR->value));
    }

    public function test_moderator_cannot_grant_specialized_access(): void
    {
        $moderator = User::factory()->create();
        $moderator->assignRole(UserRole::MODERATOR->value);

        $member = User::factory()->create();
        $member->assignRole(UserRole::CONTRIBUTOR->value);

        $application = ProjectApplication::query()->create([
            'user_id' => $member->id,
            'contribution_areas' => ['linguistics'],
            'experience' => 'Expérience de test',
            'motivation' => 'Motivation de test',
            'status' => ProjectApplicationStatus::APPROVED->value,
        ]);

        ProjectMembership::query()->create([
            'user_id' => $member->id,
            'approved_application_id' => $application->id,
            'contribution_areas' => ['linguistics'],
            'is_active' => true,
            'started_at' => now(),
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        app(ProjectApplicationService::class)->updateSpecializedAccess(
            $application,
            $moderator,
            [UserRole::VALIDATOR->value],
        );
    }
}
