<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('refund_status')->default('none')->after('verification_status');
            $table->decimal('refund_amount', 15, 2)->nullable()->after('refund_status');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            $table->text('refund_reason')->nullable()->after('refunded_at');
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn(['refund_status', 'refund_amount', 'refunded_at', 'refund_reason']);
        });
    }
};