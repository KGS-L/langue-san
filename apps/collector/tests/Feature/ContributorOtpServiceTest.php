<?php

namespace Tests\Feature;

use App\Mail\ContributorOtpMail;
use App\Services\ContributorOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ContributorOtpServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_is_sent_and_can_only_be_consumed_once(): void
    {
        Mail::fake();
        $service = app(ContributorOtpService::class);
        $email = 'contributeur@example.test';

        $service->send($email, '127.0.0.1');

        $code = null;
        Mail::assertSent(ContributorOtpMail::class, function (ContributorOtpMail $mail) use ($email, &$code): bool {
            $code = $mail->code;
            return $mail->hasTo($email) && preg_match('/^\d{8}$/', $mail->code) === 1;
        });

        $this->assertNotNull($code);
        $this->assertTrue($service->verify($email, $code));

        $this->expectException(ValidationException::class);
        $service->verify($email, $code);
    }

    public function test_wrong_code_is_rejected(): void
    {
        Mail::fake();
        $service = app(ContributorOtpService::class);
        $email = 'erreur@example.test';

        $service->send($email, '127.0.0.2');

        $this->expectException(ValidationException::class);
        $service->verify($email, '00000000');
    }
}
