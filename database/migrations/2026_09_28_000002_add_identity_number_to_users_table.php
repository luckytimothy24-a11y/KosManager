<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan identity_number pada users.
 *
 * Justifikasi audit: capability profile mensyaratkan owner dapat
 * memperbarui identity_number miliknya sendiri, tetapi kolom tersebut
 * hanya ada pada penghunis/bookings/occupants — bukan pada users.
 * Kolom nullable agar baris existing tidak terdampak dan tidak ada
 * backfill sensitif. Panjang 50 mengikuti konvensi identity pada
 * bookings (string 50) dan check-in form (maxlength 50).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('identity_number', 50)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('identity_number');
        });
    }
};
