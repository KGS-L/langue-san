<?php

namespace App\Services;

use App\Mail\ContributorOtpMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ContributorOtpService
{
    private const TTL_MINUTES = 10;
    private const MAX_VERIFY_ATTEMPTS = 5;

    public function send(string $email, ?string $ip = null): void
    {
        $email = Str::lower(trim($email));
        $rateKey = 'contributor-otp-send:'.hash('sha256', $email.'|'.($ip ?? 'unknown'));

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Trop de codes ont été demandés. Réessayez dans quelques minutes.',
            ]);
        }

        RateLimiter::hit($rateKey, 3600);

        $code = (string) random_int(10000000, 99999999);

        Cache::put($this->cacheKey($email), [
            'hash' => Hash::make($code),
            'attempts' => 0,
        ], now()->addMinutes(self::TTL_MINUTES));

        Mail::to($email)->send(new ContributorOtpMail($code, self::TTL_MINUTES));
    }

    public function verify(string $email, string $code): bool
    {
        $email = Str::lower(trim($email));
        $key = $this->cacheKey($email);
        $payload = Cache::get($key);

        if (! is_array($payload) || empty($payload['hash'])) {
            throw ValidationException::withMessages([
                'code' => 'Ce code a expiré. Demandez un nouveau code.',
            ]);
        }

        $attempts = (int) ($payload['attempts'] ?? 0);
        if ($attempts >= self::MAX_VERIFY_ATTEMPTS) {
            Cache::forget($key);
            throw ValidationException::withMessages([
                'code' => 'Trop de tentatives. Demandez un nouveau code.',
            ]);
        }

        if (! Hash::check($code, $payload['hash'])) {
            $payload['attempts'] = $attempts + 1;
            Cache::put($key, $payload, now()->addMinutes(self::TTL_MINUTES));

            throw ValidationException::withMessages([
                'code' => 'Le code saisi est incorrect.',
            ]);
        }

        Cache::forget($key);

        return true;
    }

    private function cacheKey(string $email): string
    {
        return 'contributor-otp:'.hash('sha256', $email);
    }
}
