<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\UserService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackofficeAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_transcriber_only_sees_transcription_workflow(): void
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::CONTRIBUTOR->value, UserRole::TRANSCRIBER->value);

        $this->actingAs($user)->get('/admin')->assertOk();
        $this->actingAs($user)->get('/admin/transcriptions')->assertOk();
        $this->actingAs($user)->get('/admin/validations')->assertForbidden();
        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($user)->get('/admin/exports')->assertForbidden();
    }

    public function test_validator_only_sees_validation_workflow(): void
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::CONTRIBUTOR->value, UserRole::VALIDATOR->value);

        $this->actingAs($user)->get('/admin')->assertOk();
        $this->actingAs($user)->get('/admin/validations')->assertOk();
        $this->actingAs($user)->get('/admin/transcriptions')->assertForbidden();
        $this->actingAs($user)->get('/admin/users')->assertForbidden();
    }

    public function test_plain_contributor_cannot_enter_backoffice(): void
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::CONTRIBUTOR->value);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_passwordless_login_does_not_remove_specialized_roles(): void
    {
        $user = User::factory()->create(['email' => 'linguiste@example.test']);
        $user->assignRole(UserRole::CONTRIBUTOR->value, UserRole::VALIDATOR->value);

        $resolved = app(UserService::class)->findOrCreatePasswordlessContributor('LINGUISTE@example.test');

        $this->assertTrue($resolved->hasRole(UserRole::CONTRIBUTOR->value));
        $this->assertTrue($resolved->hasRole(UserRole::VALIDATOR->value));
    }
}
