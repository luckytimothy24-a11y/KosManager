<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('identity_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('identity_rejected_at')->nullable();
            $table->text('identity_rejection_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['identity_confirmed_by']);
            $table->dropColumn(['identity_confirmed_by', 'identity_rejected_at', 'identity_rejection_reason']);
        });
    }
};
