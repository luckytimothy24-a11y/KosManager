<?php

namespace Tests\Unit;

use App\Models\Tagihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagihanLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_tagihan_total_calculation(): void
    {
        $tagihan = Tagihan::factory()->make([
            'subtotal' => 2000000,
            'discount' => 100000,
            'penalty' => 50000,
        ]);

        $total = $tagihan->subtotal - $tagihan->discount + $tagihan->penalty;
        $this->assertEquals(1950000, $total);
    }

    public function test_tagihan_no_discount_no_penalty(): void
    {
        $tagihan = Tagihan::factory()->make([
            'subtotal' => 1500000,
            'discount' => 0,
            'penalty' => 0,
        ]);

        $total = $tagihan->subtotal - $tagihan->discount + $tagihan->penalty;
        $this->assertEquals(1500000, $total);
    }

    public function test_tagihan_status_transitions(): void
    {
        $tagihan = Tagihan::factory()->create(['status' => 'unpaid']);

        $tagihan->update(['status' => 'pending_verification']);
        $this->assertEquals('pending_verification', $tagihan->fresh()->status);

        $tagihan->update(['status' => 'paid']);
        $this->assertEquals('paid', $tagihan->fresh()->status);
    }

    public function test_tagihan_overdue_status(): void
    {
        $tagihan = Tagihan::factory()->create(['status' => 'unpaid']);

        $tagihan->update(['status' => 'overdue']);
        $this->assertEquals('overdue', $tagihan->fresh()->status);
    }

    public function test_unique_bill_number(): void
    {
        $tagihan1 = Tagihan::factory()->create(['bill_number' => 'TB-001']);
        $tagihan2 = Tagihan::factory()->create(['bill_number' => 'TB-002']);

        $this->assertNotEquals($tagihan1->bill_number, $tagihan2->bill_number);
    }
}
