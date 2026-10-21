<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Booking-level payment (sebelum check-in / tagihan sewa).
     * Source of truth status tetap: verification_status (pending/approved/rejected).
     * Tidak menambah status payment baru.
     *
     * Menghindari ->change() (butuh Doctrine DBAL) — rebuild tabel portabel.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildSqlite();
        } else {
            Schema::table('pembayarans', function (Blueprint $table) {
                if (! Schema::hasColumn('pembayarans', 'booking_id')) {
                    $table->foreignId('booking_id')
                        ->nullable()
                        ->after('tagihan_id')
                        ->constrained('bookings')
                        ->nullOnDelete();
                }
            });

            // MySQL: buat kolom nullable tanpa Doctrine bila memungkinkan
            DB::statement('ALTER TABLE pembayarans MODIFY tagihan_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE pembayarans MODIFY penghuni_id BIGINT UNSIGNED NULL');

            Schema::table('pembayarans', function (Blueprint $table) {
                $table->index(['booking_id', 'verification_status']);
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildSqliteDown();
        } else {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->dropIndex(['booking_id', 'verification_status']);
                $table->dropConstrainedForeignId('booking_id');
            });

            DB::statement('ALTER TABLE pembayarans MODIFY tagihan_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE pembayarans MODIFY penghuni_id BIGINT UNSIGNED NOT NULL');
        }
    }

    private function rebuildSqlite(): void
    {
        // Index unique bernama sama tidak bisa ada di 2 tabel SQLite — drop dulu.
        try {
            DB::statement('DROP INDEX IF EXISTS pembayarans_active_payment_unique');
            DB::statement('DROP INDEX IF EXISTS pembayarans_gateway_ref_provider_unique');
        } catch (Throwable $e) {
            // ignore — index mungkin belum ada
        }

        Schema::create('pembayarans_new', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 20)->unique();
            $table->foreignId('tagihan_id')->nullable()->constrained('tagihans')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('penghuni_id')->nullable()->constrained('penghunis')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['transfer_bank', 'cash', 'e_wallet']);
            $table->string('proof_file')->nullable();
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('active_payment_key')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('gateway_provider')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->text('gateway_instructions')->nullable();
            $table->timestamp('gateway_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_id', 'verification_status']);
            $table->unique('active_payment_key', 'pembayarans_active_payment_unique');
            $table->unique(['gateway_reference', 'gateway_provider'], 'pembayarans_gateway_ref_provider_unique');
        });

        $hasBookingId = Schema::hasColumn('pembayarans', 'booking_id');
        $hasNotes = Schema::hasColumn('pembayarans', 'notes');
        $hasGateway = Schema::hasColumn('pembayarans', 'gateway_provider');
        $hasActiveKey = Schema::hasColumn('pembayarans', 'active_payment_key');

        $cols = 'id, payment_number, tagihan_id, penghuni_id, amount, payment_date, payment_method, proof_file, verification_status, admin_notes, verified_by, verified_at, created_at, updated_at, deleted_at';
        if ($hasActiveKey) {
            $cols .= ', active_payment_key';
        }
        if ($hasNotes) {
            $cols .= ', notes';
        }
        if ($hasGateway) {
            $cols .= ', gateway_provider, gateway_reference, gateway_instructions, gateway_expires_at';
        }

        DB::statement("INSERT INTO pembayarans_new (booking_id, {$cols}) SELECT NULL, {$cols} FROM pembayarans");

        Schema::dropIfExists('pembayarans');
        Schema::rename('pembayarans_new', 'pembayarans');
    }

    private function rebuildSqliteDown(): void
    {
        Schema::create('pembayarans_old', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 20)->unique();
            $table->foreignId('tagihan_id')->constrained('tagihans')->cascadeOnDelete();
            $table->foreignId('penghuni_id')->constrained('penghunis')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['transfer_bank', 'cash', 'e_wallet']);
            $table->string('proof_file')->nullable();
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('active_payment_key')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('gateway_provider')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->text('gateway_instructions')->nullable();
            $table->timestamp('gateway_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('INSERT INTO pembayarans_old SELECT id, payment_number, tagihan_id, penghuni_id, amount, payment_date, payment_method, proof_file, verification_status, active_payment_key, admin_notes, verified_by, verified_at, notes, gateway_provider, gateway_reference, gateway_instructions, gateway_expires_at, created_at, updated_at, deleted_at FROM pembayarans WHERE tagihan_id IS NOT NULL AND penghuni_id IS NOT NULL');

        Schema::dropIfExists('pembayarans');
        Schema::rename('pembayarans_old', 'pembayarans');
    }
};
