<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // kamar
        Schema::table('kamar', function (Blueprint $table) {
            $table->bigInteger('daily_price_rp')->nullable()->after('daily_price');
            $table->bigInteger('weekly_price_rp')->nullable()->after('weekly_price');
            $table->bigInteger('monthly_price_rp')->nullable()->after('monthly_price');
        });

        // tagihans
        Schema::table('tagihans', function (Blueprint $table) {
            $table->bigInteger('subtotal_rp')->nullable()->after('subtotal');
            $table->bigInteger('discount_rp')->nullable()->after('discount');
            $table->bigInteger('down_payment_discount_rp')->nullable()->after('down_payment_discount');
            $table->bigInteger('penalty_rp')->nullable()->after('penalty');
            $table->bigInteger('total_rp')->nullable()->after('total');
            $table->bigInteger('total_room_bill_rp')->nullable()->after('total_room_bill');
        });

        // pembayarans
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->bigInteger('amount_rp')->nullable()->after('amount');
            $table->bigInteger('rent_amount_rp')->nullable()->after('rent_amount');
            $table->bigInteger('penalty_amount_rp')->nullable()->after('penalty_amount');
            $table->bigInteger('refund_amount_rp')->nullable()->after('refund_amount');
        });

        // kontraks
        Schema::table('kontraks', function (Blueprint $table) {
            $table->bigInteger('rental_price_rp')->nullable()->after('rental_price');
            $table->bigInteger('custom_share_amount_rp')->nullable()->after('custom_share_amount');
            $table->bigInteger('down_payment_amount_rp')->nullable()->after('down_payment_amount');
        });

        // bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->bigInteger('price_rp')->nullable()->after('price');
        });

        // Backfill from decimal columns (rupiah = whole units).
        $this->backfill('kamar', [
            'daily_price' => 'daily_price_rp',
            'weekly_price' => 'weekly_price_rp',
            'monthly_price' => 'monthly_price_rp',
        ]);
        $this->backfill('tagihans', [
            'subtotal' => 'subtotal_rp',
            'discount' => 'discount_rp',
            'down_payment_discount' => 'down_payment_discount_rp',
            'penalty' => 'penalty_rp',
            'total' => 'total_rp',
            'total_room_bill' => 'total_room_bill_rp',
        ]);
        $this->backfill('pembayarans', [
            'amount' => 'amount_rp',
            'rent_amount' => 'rent_amount_rp',
            'penalty_amount' => 'penalty_amount_rp',
            'refund_amount' => 'refund_amount_rp',
        ]);
        $this->backfill('kontraks', [
            'rental_price' => 'rental_price_rp',
            'custom_share_amount' => 'custom_share_amount_rp',
            'down_payment_amount' => 'down_payment_amount_rp',
        ]);
        $this->backfill('bookings', [
            'price' => 'price_rp',
        ]);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('price_rp');
        });
        Schema::table('kontraks', function (Blueprint $table) {
            $table->dropColumn(['rental_price_rp', 'custom_share_amount_rp', 'down_payment_amount_rp']);
        });
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn(['amount_rp', 'rent_amount_rp', 'penalty_amount_rp', 'refund_amount_rp']);
        });
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropColumn(['subtotal_rp', 'discount_rp', 'down_payment_discount_rp', 'penalty_rp', 'total_rp', 'total_room_bill_rp']);
        });
        Schema::table('kamar', function (Blueprint $table) {
            $table->dropColumn(['daily_price_rp', 'weekly_price_rp', 'monthly_price_rp']);
        });
    }

    private function backfill(string $table, array $map): void
    {
        foreach ($map as $from => $to) {
            DB::statement("UPDATE {$table} SET {$to} = CAST(ROUND(COALESCE({$from}, 0)) AS INTEGER) WHERE {$from} IS NOT NULL");
        }
    }
};
