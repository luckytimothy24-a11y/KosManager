<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

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
}
