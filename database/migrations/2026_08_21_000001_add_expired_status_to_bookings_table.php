<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan status 'expired' pada tabel bookings
     * (rebuild tabel karena SQLite tidak mendukung ALTER untuk enum/check).
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('bookings_new', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kos_id')->constrained('kos')->onDelete('cascade');
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('cascade');
            $table->date('booking_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('rental_type', ['daily', 'monthly']);
            $table->decimal('price', 12, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed', 'expired'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement(
            'INSERT INTO bookings_new (id, booking_code, user_id, kos_id, kamar_id, booking_date, start_date, end_date, rental_type, price, status, notes, created_at, updated_at, deleted_at)
             SELECT id, booking_code, user_id, kos_id, kamar_id, booking_date, start_date, end_date, rental_type, price, status, notes, created_at, updated_at, deleted_at FROM bookings'
        );

        Schema::dropIfExists('bookings');
        Schema::rename('bookings_new', 'bookings');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('bookings_old', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kos_id')->constrained('kos')->onDelete('cascade');
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('cascade');
            $table->date('booking_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('rental_type', ['daily', 'monthly']);
            $table->decimal('price', 12, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement(
            "INSERT INTO bookings_old (id, booking_code, user_id, kos_id, kamar_id, booking_date, start_date, end_date, rental_type, price, status, notes, created_at, updated_at, deleted_at)
             SELECT id, booking_code, user_id, kos_id, kamar_id, booking_date, start_date, end_date, rental_type, price,
                    CASE WHEN status = 'expired' THEN 'cancelled' ELSE status END,
                    notes, created_at, updated_at, deleted_at FROM bookings"
        );

        Schema::dropIfExists('bookings');
        Schema::rename('bookings_old', 'bookings');

        Schema::enableForeignKeyConstraints();
    }
};
