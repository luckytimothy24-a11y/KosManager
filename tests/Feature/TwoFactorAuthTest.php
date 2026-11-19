<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    private TwoFactorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TwoFactorService::class);
    }

    public function test_totp_service_generates_and_verifies_codes(): void
    {
        $secret = $this->service->generateSecret();

        $this->assertSame(32, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);

        $code = $this->service->code($secret);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertTrue($this->service->verify($secret, $code));
        $this->assertFalse($this->service->verify($secret, '000000') && $code !== '000000');
    }

    public function test_base32_roundtrip(): void
    {
        $binary = random_bytes(20);
        $encoded = $this->service->base32Encode($binary);

        $this->assertSame($binary, $this->service->base32Decode($encoded));
    }

    public function test_enable_generates_pending_secret(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->post(route('two-factor.enable'));

        $response->assertRedirect(route('two-factor.confirm'));

        $fresh = $user->fresh();
        $this->assertNotNull($fresh->two_factor_secret);
        $this->assertNull($fresh->two_factor_confirmed_at);
        $this->assertFalse($fresh->hasTwoFactorEnabled());
    }

    public function test_confirm_with_valid_code_activates_2fa_and_generates_recovery_codes(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $this->actingAs($user)->post(route('two-factor.enable'));
        $secret = $user->fresh()->two_factor_secret;

        $response = $this->actingAs($user)->post(route('two-factor.confirm.store'), [
            'code' => $this->service->code($secret),
        ]);

        $response->assertRedirect(route('two-factor.index'));

        $fresh = $user->fresh();
        $this->assertTrue($fresh->hasTwoFactorEnabled());
        $this->assertCount(8, $fresh->two_factor_recovery_codes);
    }

    public function test_confirm_with_invalid_code_fails(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $this->actingAs($user)->post(route('two-factor.enable'));

        $response = $this->actingAs($user)->post(route('two-factor.confirm.store'), [
            'code' => '000000',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_user_with_2fa_is_redirected_to_challenge(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $secret = $this->service->generateSecret();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $this->service->generateRecoveryCodes(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('two-factor.challenge'));
    }

    public function test_challenge_with_valid_totp_grants_access(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $secret = $this->service->generateSecret();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $this->service->generateRecoveryCodes(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($user)->post(route('two-factor.challenge.verify'), [
            'code' => $this->service->code($secret),
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertSame(now()->timestamp, session('two_factor_verified_at'));

        // Setelah verifikasi pada sesi ini, dashboard dapat diakses.
        $this->actingAs($user)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_challenge_with_recovery_code_consumes_it(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $codes = $this->service->generateRecoveryCodes();
        $user->forceFill([
            'two_factor_secret' => $this->service->generateSecret(),
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($user)->post(route('two-factor.challenge.verify'), [
            'code' => $codes[0],
        ]);

        $response->assertRedirect(route('dashboard'));

        $remaining = $user->fresh()->two_factor_recovery_codes;
        $this->assertCount(7, $remaining);
        $this->assertNotContains($codes[0], $remaining);
    }

    public function test_challenge_with_invalid_code_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $user->forceFill([
            'two_factor_secret' => $this->service->generateSecret(),
            'two_factor_recovery_codes' => $this->service->generateRecoveryCodes(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($user)->post(route('two-factor.challenge.verify'), [
            'code' => '999999',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull(session('two_factor_verified_at'));
    }

    public function test_disable_requires_correct_password_and_clears_secret(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'password' => bcrypt('password'),
        ]);

        $user->forceFill([
            'two_factor_secret' => $this->service->generateSecret(),
            'two_factor_recovery_codes' => $this->service->generateRecoveryCodes(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($user)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->post(route('two-factor.disable'), ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());

        $this->actingAs($user)
            ->withSession(['two_factor_verified_at' => now()->timestamp])
            ->post(route('two-factor.disable'), ['password' => 'password'])
            ->assertRedirect(route('two-factor.index'));

        $fresh = $user->fresh();
        $this->assertNull($fresh->two_factor_secret);
        $this->assertNull($fresh->two_factor_confirmed_at);
        $this->assertFalse($fresh->hasTwoFactorEnabled());
    }

    public function test_secret_is_encrypted_at_rest(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $this->actingAs($user)->post(route('two-factor.enable'));

        $secret = $user->fresh()->two_factor_secret;
        $raw = DB::table('users')->where('id', $user->id)->value('two_factor_secret');

        $this->assertNotNull($secret);
        $this->assertNotSame($secret, $raw);
        $this->assertSame($secret, $user->fresh()->two_factor_secret);
    }

    public function test_guest_cannot_access_two_factor_pages(): void
    {
        $this->get(route('two-factor.index'))->assertRedirect(route('login'));
        $this->get(route('two-factor.challenge'))->assertRedirect(route('login'));
    }
}
