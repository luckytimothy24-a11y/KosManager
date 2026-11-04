<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_audit_log_chain_can_be_verified_after_parallel_writes()
    {
        // Create a user first (FK constraint requires it)
        $user = User::factory()->create();

        // Configure HMAC secret
        config(['audit.hmac_secret' => str_repeat('a', 64)]);

        // Create initial log entry via service (sets integrity_hash properly)
        $initial = AuditLogService::log('Test', 'TestModule', 'Initial entry', $user->id);

        // Simulate parallel/sequential concurrent writes using the service
        // Each call to log() reads the latest hash and creates a chainable entry
        for ($i = 1; $i <= 20; $i++) {
            AuditLogService::log('ConcurrentTest', 'TestModule', "Entry $i", $user->id);
        }

        // Now verify the entire chain
        $result = AuditLogService::verifyChain();

        // The chain should be valid - all integrity hashes should match
        $this->assertTrue($result['valid'], 'Audit log chain should be valid: '.json_encode($result));
        $this->assertEquals(21, $result['total'], 'Should have 21 total entries (1 initial + 20 writes)');
        $this->assertEquals(21, $result['verified'], 'Should have 21 verified entries (all entries have integrity hashes)');
    }

    /** @test */
    public function test_verify_chain_detects_race_condition_break()
    {
        // Create a user first
        $user = User::factory()->create();

        config(['audit.hmac_secret' => str_repeat('a', 64)]);

        // Create initial log entry
        $log1 = AuditLogService::log('First', 'Test', 'First entry', $user->id);

        // Create second log normally
        AuditLogService::log('Second', 'Test', 'Second entry', $user->id);

        // Simulate a race condition where third log reads stale previous_hash
        // by directly creating a log with a manipulated previous_hash
        $previousHash = AuditLog::whereNotNull('integrity_hash')
            ->latest('id')
            ->value('integrity_hash');

        // Create third log with manipulated data - bypassing the service's hash computation
        $log3 = AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Third',
            'module' => 'Test',
            'description' => 'Third entry',
            'ip_address' => '127.0.0.1',
            'previous_hash' => $previousHash,
            'integrity_hash' => null,
        ]);

        // Overwrite the integrity_hash with an incorrect value
        $log3->update(['integrity_hash' => ' '.str_repeat('0', 63)]);

        // Verify chain should detect the break
        $result = AuditLogService::verifyChain();

        // This should detect the broken chain
        $this->assertFalse($result['valid'], 'Audit log chain should be invalid when broken by race condition');
        $this->assertNotNull($result['failed_id'], 'Should have a failed_id when chain is broken');
    }
}
