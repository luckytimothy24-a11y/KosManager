<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Mitigasi CVE-2026-48019 / GHSA-5vg9-5847-vvmq untuk Laravel 10.
 *
 * Laravel 12.60 menambahkan penolakan CR/LF pada rule email. Karena
 * aplikasi masih di Laravel 10, perilaku itu di-backport lewat SafeValidator.
 *
 * Payload memakai quoted local-part karena egulias/email-validator menerima
 * folding whitespace (CRLF) di dalam quoted string, sementara alamat biasa
 * sudah ditolak lebih dulu.
 */
class SafeEmailValidationTest extends TestCase
{
    private function crlfPayload(): string
    {
        return "\"victim\r\nBcc: attacker@example.com\"@example.com";
    }

    public function test_email_rule_rejects_crlf_injection(): void
    {
        $validator = Validator::make(
            ['email' => $this->crlfPayload()],
            ['email' => ['required', 'email']]
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_email_rule_rejects_crlf_in_quoted_email(): void
    {
        $validator = Validator::make(
            ['email' => "\"victim@example.com\r\nBcc: attacker@example.com\"@example.com"],
            ['email' => ['required', 'email']]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_email_rule_still_accepts_valid_address(): void
    {
        $validator = Validator::make(
            ['email' => 'tenant@example.com'],
            ['email' => ['required', 'email']]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_email_rule_with_parameters_still_works(): void
    {
        $validator = Validator::make(
            ['email' => 'tenant@example.com'],
            ['email' => ['required', 'email:rfc']]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_email_rule_with_parameters_rejects_crlf(): void
    {
        $validator = Validator::make(
            ['email' => $this->crlfPayload()],
            ['email' => ['required', 'email:rfc']]
        );

        $this->assertTrue($validator->fails());
    }
}
