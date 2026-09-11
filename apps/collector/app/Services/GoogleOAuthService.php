<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleOAuthService
{
    public function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    public function authorizationUrl(Request $request): string
    {
        $this->ensureConfigured();

        $state = Str::random(48);
        $request->session()->put('contributor_google_state', $state);

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => route('contributor.auth.google.callback'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
            'access_type' => 'online',
        ]);
    }

    public function userFromCallback(Request $request): array
    {
        $this->ensureConfigured();

        $expectedState = (string) $request->session()->pull('contributor_google_state');
        $receivedState = (string) $request->query('state');

        if ($expectedState === '' || ! hash_equals($expectedState, $receivedState)) {
            throw ValidationException::withMessages([
                'google' => 'La connexion Google a expiré. Veuillez réessayer.',
            ]);
        }

        $code = (string) $request->query('code');
        if ($code === '') {
            throw ValidationException::withMessages([
                'google' => 'Google n’a pas retourné de code de connexion.',
            ]);
        }

        try {
            $token = Http::asForm()->throw()->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => route('contributor.auth.google.callback'),
            ])->json();

            $profile = Http::withToken((string) ($token['access_token'] ?? ''))
                ->acceptJson()
                ->throw()
                ->get('https://openidconnect.googleapis.com/v1/userinfo')
                ->json();
        } catch (RequestException) {
            throw ValidationException::withMessages([
                'google' => 'Impossible de terminer la connexion Google pour le moment.',
            ]);
        }

        if (empty($profile['email']) || ! ($profile['email_verified'] ?? false)) {
            throw ValidationException::withMessages([
                'google' => 'Votre adresse email Google n’a pas pu être vérifiée.',
            ]);
        }

        return [
            'email' => Str::lower((string) $profile['email']),
            'name' => trim((string) ($profile['name'] ?? '')),
        ];
    }

    private function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw ValidationException::withMessages([
                'google' => 'La connexion Google n’est pas encore configurée sur ce serveur.',
            ]);
        }
    }
}
