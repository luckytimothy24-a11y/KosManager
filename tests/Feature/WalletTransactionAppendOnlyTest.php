<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\EscrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class WalletTransactionAppendOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_transaction_rejects_update(): void
    {
        $owner = User::factory()->create();
        $tx = WalletTransaction::create([
            'owner_id' => $owner->id,
            'type' => 'escrow_in',
            'amount' => 100000,
            'description' => 'test',
            'status' => 'pending',
        ]);

        $this->expectException(\RuntimeException::class);
        $tx->update(['status' => 'cancelled']);
    }

    public function test_wallet_transaction_rejects_delete(): void
    {
        $owner = User::factory()->create();
        $tx = WalletTransaction::create([
            'owner_id' => $owner->id,
            'type' => 'escrow_in',
            'amount' => 100000,
            'description' => 'test',
            'status' => 'pending',
        ]);

        $this->expectException(\RuntimeException::class);
        $tx->delete();
    }

    public function test_escrow_service_no_longer_exposes_process_refund(): void
    {
        $reflection = new ReflectionClass(EscrowService::class);

        $this->assertFalse(
            $reflection->hasMethod('processRefund'),
            'EscrowService::processRefund() harus dihapus (dead code yang melewati guard append-only).'
        );
    }
}
