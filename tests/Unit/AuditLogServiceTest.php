<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['audit.hmac_secret' => str_repeat('a', 64)]);
    }

    public function test_creates_audit_log_entry(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AuditLogService::log('Create', 'Kos', 'Membuat kos baru', $user->id, ['name' => 'Kos Test']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'Create',
            'module' => 'Kos',
            'description' => 'Membuat kos baru',
        ]);
    }

    public function test_login_creates_audit_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AuditLogService::login($user);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'Login',
            'module' => 'Auth',
        ]);
    }

    public function test_approve_creates_audit_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AuditLogService::approve('Booking', 'Booking BK001 disetujui');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'Approve',
            'module' => 'Booking',
            'description' => 'Booking BK001 disetujui',
        ]);
    }

    public function test_reject_creates_audit_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AuditLogService::reject('Booking', 'Booking BK002 ditolak');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Reject',
            'module' => 'Booking',
        ]);
    }

    public function test_update_creates_audit_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AuditLogService::update('Kamar', 'Kamar 101 diperbarui', ['room_number' => '101']);

        $log = AuditLog::where('action', 'Update')->where('module', 'Kamar')->first();
        $this->assertNotNull($log);
        $this->assertEquals(['room_number' => '101'], $log->timestamp_data);
    }

    public function test_delete_creates_audit_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AuditLogService::delete('Fasilitas', 'Fasilitas WiFi dihapus');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Delete',
            'module' => 'Fasilitas',
        ]);
    }

    public function test_audit_log_stores_ip_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AuditLogService::log('Test', 'Test', 'Testing IP');

        $log = AuditLog::where('action', 'Test')->first();
        $this->assertNotNull($log->ip_address);
    }

    public function test_single_log_has_integrity_hash_and_previous_hash_null(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $log = AuditLogService::log('Test', 'Test', 'First log entry');

        $this->assertNotNull($log->integrity_hash);
        $this->assertNull($log->previous_hash);
        $this->assertEquals(64, strlen($log->integrity_hash));
    }

    public function test_chain_previous_hash_points_to_previous_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $log1 = AuditLogService::log('Test', 'Test', 'First log');
        $log2 = AuditLogService::log('Test', 'Test', 'Second log');
        $log3 = AuditLogService::log('Test', 'Test', 'Third log');

        $this->assertNull($log1->previous_hash);
        $this->assertEquals($log1->integrity_hash, $log2->previous_hash);
        $this->assertEquals($log2->integrity_hash, $log3->previous_hash);
    }

    public function test_verify_chain_returns_valid_when_intact(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AuditLogService::log('Test', 'Test', 'Log one');
        AuditLogService::log('Test', 'Test', 'Log two');
        AuditLogService::log('Test', 'Test', 'Log three');

        $result = AuditLogService::verifyChain();

        $this->assertTrue($result['valid']);
        $this->assertEquals(3, $result['total']);
        $this->assertEquals(3, $result['verified']);
        $this->assertNull($result['failed_id']);
    }

    public function test_verify_chain_detects_tampering(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AuditLogService::log('Test', 'Test', 'Log one');
        AuditLogService::log('Test', 'Test', 'Log two');

        AuditLog::where('module', 'Test')->first()->update(['description' => 'TAMPERED']);

        $result = AuditLogService::verifyChain();

        $this->assertFalse($result['valid']);
        $this->assertNotNull($result['failed_id']);
    }

    public function test_verify_chain_skips_null_hash_entries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AuditLogService::log('Test', 'Test', 'Log one');

        AuditLog::where('module', 'Test')->first()->update(['integrity_hash' => null]);

        AuditLogService::log('Test', 'Test', 'Log two');

        $result = AuditLogService::verifyChain();

        $this->assertTrue($result['valid']);
        $this->assertEquals(1, $result['verified']);
    }

    public function test_verify_chain_detects_timestamp_data_tampering(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AuditLogService::update('Kamar', 'Kamar 101', ['room_number' => '101']);
        AuditLogService::log('Test', 'Test', 'Log two');

        AuditLog::where('module', 'Kamar')->first()->update([
            'timestamp_data' => ['room_number' => '999'],
        ]);

        $result = AuditLogService::verifyChain();

        $this->assertFalse($result['valid']);
        $this->assertNotNull($result['failed_id']);
    }

    public function test_chain_fails_with_wrong_secret(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        config(['audit.hmac_secret' => str_repeat('a', 64)]);
        AuditLogService::log('Test', 'Test', 'Log one');
        $firstHash = AuditLog::latest('id')->first()->integrity_hash;

        config(['audit.hmac_secret' => str_repeat('b', 64)]);
        AuditLogService::log('Test', 'Test', 'Log two');
        $secondHash = AuditLog::latest('id')->first()->integrity_hash;

        $this->assertNotEquals($firstHash, $secondHash);

        $result = AuditLogService::verifyChain();

        $this->assertFalse($result['valid']);
    }

    public function test_fail_closed_when_no_hmac_secret_configured(): void
    {
        config(['audit.hmac_secret' => null]);

        $user = User::factory()->create();

        $this->actingAs($user);

        $log = AuditLogService::log('Test', 'Test', 'No secret log');

        $this->assertNotNull($log->integrity_hash);
        $this->assertEquals(64, strlen($log->integrity_hash));

        $result = AuditLogService::verifyChain();
        $this->assertTrue($result['valid']);
    }

    public function test_audit_logs_schema_has_integrity_columns_after_migration(): void
    {
        $this->assertTrue(Schema::hasTable('audit_logs'));
        $this->assertTrue(Schema::hasColumn('audit_logs', 'integrity_hash'));
        $this->assertTrue(Schema::hasColumn('audit_logs', 'previous_hash'));
        $this->assertTrue(Schema::hasColumn('audit_logs', 'timestamp_data'));
        $this->assertTrue(Schema::hasColumn('audit_logs', 'created_at'));
        $this->assertTrue(Schema::hasColumn('audit_logs', 'updated_at'));
    }

    public function test_migration_schema_supports_audit_integrity_roundtrip(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $first = AuditLogService::log('Test', 'Test', 'Roundtrip one');
        $second = AuditLogService::log('Test', 'Test', 'Roundtrip two');

        $this->assertNotNull($first->integrity_hash);
        $this->assertEquals(64, strlen($first->integrity_hash));
        $this->assertEquals($first->integrity_hash, $second->previous_hash);

        $result = AuditLogService::verifyChain();
        $this->assertTrue($result['valid']);
        $this->assertEquals(2, $result['verified']);
    }
}
