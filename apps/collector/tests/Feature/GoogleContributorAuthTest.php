<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Services\UserService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleContributorAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        config()->set('services.google.client_id', 'google-client-test');
        config()->set('services.google.client_secret', 'google-secret-test');
    }

    public function test_google_login_reuses_existing_passwordless_account_with_same_email(): void
    {
        $existing = app(UserService::class)->findOrCreatePasswordlessContributor('same@example.test');
        $this->assertTrue($existing->hasRole(UserRole::CONTRIBUTOR->value));
        Auth::logout();

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'token-test',
            ]),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'email' => 'SAME@example.test',
                'email_verified' => true,
                'name' => 'Même Personne',
            ]),
        ]);

        $response = $this
            ->withSession(['contributor_google_state' => 'secure-state'])
            ->get('/compte/google/callback?state=secure-state&code=google-code');

        $response->assertRedirect(route('contributor.profile.edit'));
        $this->assertAuthenticatedAs($existing->fresh());
        $this->assertDatabaseCount('users', 1);
    }

    public function test_google_callback_rejects_invalid_state(): void
    {
        $response = $this
            ->withSession(['contributor_google_state' => 'expected-state'])
            ->get('/compte/google/callback?state=wrong-state&code=google-code');

        $response->assertSessionHasErrors('google');
        $this->assertGuest();
    }
}
