<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel pesan sederhana antara Tenant (penghuni) ↔ Owner (pemilik kos).
     * Satu baris = satu pesan. Percakapan ditentukan pasangan (sender, receiver)
     * pada satu kos tertentu. Tidak memakai websocket/realtime — refresh halaman
     * sudah cukup untuk kebutuhan bantuan penghuni.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kos_id')->constrained('kos')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->string('category', 30)->nullable()->comment('kamar/fasilitas/tagihan/pembayaran/booking/kontrak/lainnya');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['kos_id', 'sender_id', 'receiver_id']);
            $table->index(['receiver_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
