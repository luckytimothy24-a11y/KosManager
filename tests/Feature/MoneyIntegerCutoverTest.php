<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * FASE 1D — CUTOVER INTEGER RUPIAH.
 *
 * Memastikan accessor mengembalikan integer rupiah, mutator menulis kolom
 * `*_rp` dan kolom decimal lama secara sinkron, serta fallback tetap aman
 * untuk baris legacy yang belum memiliki nilai integer.
 */
class MoneyIntegerCutoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_money_accessors_return_integer_rupiah(): void
    {
        $kamar = Kamar::factory()->create([
            'daily_price' => 100000,
            'weekly_price' => 400000,
            'monthly_price' => 1500000,
        ]);

        $this->assertSame(100000, $kamar->daily_price);
        $this->assertSame(400000, $kamar->weekly_price);
        $this->assertSame(1500000, $kamar->monthly_price);

        $tagihan = Tagihan::factory()->create([
            'subtotal' => 1000000,
            'discount' => 100000,
            'down_payment_discount' => 50000,
            'penalty' => 50000,
            'total' => 900000,
            'total_room_bill' => 1000000,
        ]);

        $this->assertSame(1000000, $tagihan->subtotal);
        $this->assertSame(100000, $tagihan->discount);
        $this->assertSame(50000, $tagihan->down_payment_discount);
        $this->assertSame(50000, $tagihan->penalty);
        $this->assertSame(900000, $tagihan->total);
        $this->assertSame(1000000, $tagihan->total_room_bill);
    }

    public function test_money_mutators_write_integer_and_decimal_columns(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'amount' => 1234567,
            'rent_amount' => 1200000,
            'penalty_amount' => 34567,
            'refund_amount' => 0,
        ]);

        $row = DB::table('pembayarans')->where('id', $pembayaran->id)->first();

        $this->assertSame(1234567, (int) $row->amount_rp);
        $this->assertSame(1200000, (int) $row->rent_amount_rp);
        $this->assertSame(34567, (int) $row->penalty_amount_rp);
        $this->assertSame(0, (int) $row->refund_amount_rp);

        // Kolom decimal lama tetap sinkron untuk rollback/legacy query.
        $this->assertEqualsWithDelta(1234567.0, (float) $row->amount, 0.001);
        $this->assertEqualsWithDelta(1200000.0, (float) $row->rent_amount, 0.001);
        $this->assertEqualsWithDelta(34567.0, (float) $row->penalty_amount, 0.001);
    }

    public function test_updating_money_keeps_both_columns_in_sync(): void
    {
        $tagihan = Tagihan::factory()->create(['total' => 1000000]);

        $tagihan->update(['total' => 1250000]);

        $row = DB::table('tagihans')->where('id', $tagihan->id)->first();

        $this->assertSame(1250000, $tagihan->fresh()->total);
        $this->assertSame(1250000, (int) $row->total_rp);
        $this->assertEqualsWithDelta(1250000.0, (float) $row->total, 0.001);
    }

    public function test_accessor_falls_back_to_decimal_when_integer_column_is_null(): void
    {
        $kamar = Kamar::factory()->create(['monthly_price' => 900000]);

        DB::table('kamar')->where('id', $kamar->id)->update(['monthly_price_rp' => null]);

        $this->assertSame(900000, $kamar->fresh()->monthly_price);
    }

    public function test_fractional_decimal_is_rounded_to_whole_rupiah_on_write(): void
    {
        $tagihan = Tagihan::factory()->create(['total' => 999999.6]);

        $this->assertSame(1000000, $tagihan->total);
        $this->assertSame(1000000, (int) DB::table('tagihans')->where('id', $tagihan->id)->value('total_rp'));
    }

    public function test_direct_integer_column_write_syncs_decimal_column(): void
    {
        $kontrak = Kontrak::factory()->create([
            'rental_price' => 1500000,
            'down_payment_amount' => 100000,
            'custom_share_amount' => 0,
        ]);

        $kontrak->forceFill(['rental_price_rp' => 1750000])->save();

        $row = DB::table('kontraks')->where('id', $kontrak->id)->first();

        $this->assertSame(1750000, $kontrak->fresh()->rental_price);
        $this->assertEqualsWithDelta(1750000.0, (float) $row->rental_price, 0.001);
    }

    public function test_booking_money_uses_integer_columns(): void
    {
        $booking = Booking::factory()->create([
            'price' => 1500000,
            'down_payment_amount' => 100000,
        ]);

        $row = DB::table('bookings')->where('id', $booking->id)->first();

        $this->assertSame(1500000, $booking->price);
        $this->assertSame(100000, $booking->down_payment_amount);
        $this->assertSame(1500000, (int) $row->price_rp);
        $this->assertSame(100000, (int) $row->down_payment_amount_rp);
    }
}
