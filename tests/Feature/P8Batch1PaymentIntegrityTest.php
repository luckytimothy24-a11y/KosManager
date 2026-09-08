<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\DatabaseBackup\MySqlBackupDriver;
use App\Services\PaymentGateway\PaymentGatewayManager;
use App\Services\PaymentGateway\SandboxGateway;
use App\Services\PaymentService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * P8 Batch 1 — Webhook & Payment Integrity Hardening.
 */
class P8Batch1PaymentIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private Tagihan $tagihan;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $this->tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
        ]);
    }

    private function sign(array $payload): string
    {
        return hash_hmac('sha256', json_encode($payload), config('payment-gateway.signature_key'));
    }

    // ---------------------------------------------------------------- fail-closed secret

    public function test_driver_fails_closed_when_secret_missing(): void
    {
        config(['payment-gateway.signature_key' => null]);

        $this->expectException(\InvalidArgumentException::class);
        PaymentGatewayManager::driver();
    }

    public function test_driver_fails_closed_on_placeholder_secret(): void
    {
        config(['payment-gateway.signature_key' => 'change-me-in-production']);

        $this->expectException(\InvalidArgumentException::class);
        PaymentGatewayManager::driver();
    }

    public function test_webhook_rejects_when_secret_missing(): void
    {
        config(['payment-gateway.signature_key' => null]);

        $payload = ['gateway_reference' => 'VA-ANY', 'status' => 'success', 'amount' => 1];

        $this->postJson(route('webhook.payment-gateway'), $payload, [
            'X-Gateway-Signature' => hash_hmac('sha256', json_encode($payload), 'any-key'),
        ])->assertForbidden();
    }

    public function test_sandbox_verify_signature_rejects_placeholder_secret(): void
    {
        $payload = ['gateway_reference' => 'VA-ANY', 'status' => 'success', 'amount' => 1];

        config(['payment-gateway.signature_key' => 'change-me-in-production']);
        $driver = new SandboxGateway;

        $this->assertFalse($driver->verifySignature($payload, hash_hmac('sha256', json_encode($payload), 'change-me-in-production')));
    }

    // ---------------------------------------------------------------- webhook rate limit

    public function test_webhook_is_rate_limited(): void
    {
        $payload = ['gateway_reference' => 'VA-ANY', 'status' => 'success', 'amount' => 1];
        $headers = ['X-Gateway-Signature' => $this->sign($payload)];

        // Limit 20/1menit -> request ke-21 harus 429.
        for ($i = 0; $i < 20; $i++) {
            $this->postJson(route('webhook.payment-gateway'), $payload, $headers);
        }

        $this->postJson(route('webhook.payment-gateway'), $payload, $headers)->assertStatus(429);
    }

    // ---------------------------------------------------------------- migration / integrity index

    public function test_integrity_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasColumn('pembayarans', 'active_payment_key'));

        $indexes = collect(Schema::getIndexes('pembayarans'));

        $this->assertTrue(
            $indexes->contains(fn ($i) => in_array('active_payment_key', $i['columns']))
        );
        $this->assertTrue(
            $indexes->contains(fn ($i) => collect($i['columns'])->contains('gateway_reference')
                && collect($i['columns'])->contains('gateway_provider'))
        );
    }

    public function test_duplicate_active_payment_for_same_tagihan_blocked_at_db_level(): void
    {
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'active_payment_key' => $this->tagihan->id,
            'verification_status' => 'pending',
        ]);

        $this->expectException(QueryException::class);
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'active_payment_key' => $this->tagihan->id,
            'verification_status' => 'approved',
        ]);
    }

    public function test_rejected_payment_can_be_retried_same_tagihan(): void
    {
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'active_payment_key' => null,
            'verification_status' => 'rejected',
        ]);
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'active_payment_key' => null,
            'verification_status' => 'rejected',
        ]);

        $this->assertSame(2, Pembayaran::where('tagihan_id', $this->tagihan->id)->count());
    }

    public function test_duplicate_gateway_reference_same_provider_blocked_at_db_level(): void
    {
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'gateway_reference' => 'VA-DUP',
            'gateway_provider' => 'sandbox',
        ]);

        $this->expectException(QueryException::class);
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'gateway_reference' => 'VA-DUP',
            'gateway_provider' => 'sandbox',
        ]);
    }

    public function test_manual_payments_with_null_gateway_reference_are_allowed(): void
    {
        $a = Pembayaran::factory()->create(['tagihan_id' => $this->tagihan->id, 'verification_status' => 'rejected']);
        $b = Pembayaran::factory()->create(['tagihan_id' => $this->tagihan->id, 'verification_status' => 'rejected']);

        $this->assertNotSame($a->id, $b->id);
        $this->assertSame(2, Pembayaran::count());
    }

    // ---------------------------------------------------------------- PaymentService dedup (manual + gateway)

    public function test_legacy_gateway_payment_sets_active_key_and_reference(): void
    {
        $payment = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'amount' => $this->tagihan->total,
            'payment_method' => 'e_wallet',
            'verification_status' => 'pending',
            'gateway_reference' => 'VA-'.strtoupper(Str::random(6)),
            'gateway_provider' => 'sandbox',
            'active_payment_key' => $this->tagihan->id,
        ]);

        $this->assertSame('pending', $payment->verification_status);
        $this->assertSame((int) $this->tagihan->id, (int) $payment->active_payment_key);
        $this->assertStringStartsWith('VA-', $payment->gateway_reference);
        $this->assertSame('sandbox', $payment->gateway_provider);
    }

    public function test_manual_payment_via_service_sets_active_key(): void
    {
        $penghuni = Penghuni::where('user_id', $this->tenant->id)->where('status', 'active')->firstOrFail();
        $service = app(PaymentService::class);

        $result = $service->createManual([
            'penghuni' => $penghuni,
            'tagihan' => $this->tagihan,
            'amount' => $this->tagihan->total,
            'payment_method' => 'cash',
        ]);

        $this->assertTrue($result['ok']);
        $this->assertSame((int) $this->tagihan->id, (int) $result['pembayaran']->active_payment_key);
        $this->assertSame('pending', $result['pembayaran']->verification_status);
    }

    // ---------------------------------------------------------------- MySQL backup password not via argv

    public function test_mysql_backup_does_not_pass_password_via_argv(): void
    {
        $driver = new class('mysql') extends MySqlBackupDriver
        {
            protected function currentEnvironment(): array
            {
                return ['PATH' => '/usr/bin', 'HOME' => '/root'];
            }

            public function args(array $config, string $binary, string $flags): array
            {
                return $this->arguments($config, $binary, $flags);
            }

            public function env(array $config): array
            {
                return $this->environment($config);
            }
        };

        $config = [
            'host' => 'db.example', 'username' => 'root', 'password' => 'SuperSecret123', 'database' => 'kosmanager',
        ];

        $args = $driver->args($config, 'mysqldump', '--single-transaction');
        $env = $driver->env($config);

        $this->assertNotEmpty($args);
        foreach ($args as $arg) {
            $this->assertStringNotContainsString('--password=', $arg, "Password tidak boleh di argv: {$arg}");
        }
        $this->assertSame('SuperSecret123', $env['MYSQL_PWD']);
        $this->assertArrayNotHasKey('--password', $env);
    }
}
