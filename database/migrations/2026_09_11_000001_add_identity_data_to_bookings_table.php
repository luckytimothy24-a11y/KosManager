<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('identity_number', 50)->nullable()->after('identity_confirmed_at');
            $table->string('phone', 20)->nullable()->after('identity_number');
            $table->text('address')->nullable()->after('phone');
            $table->timestamp('identity_data_submitted_at')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['identity_number', 'phone', 'address', 'identity_data_submitted_at']);
        });
    }
};
