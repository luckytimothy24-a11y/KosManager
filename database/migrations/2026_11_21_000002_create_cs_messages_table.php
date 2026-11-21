<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu baris = satu pesan CS. `receiver_id` NULL berarti pesan masuk
     * ke inbox bersama Admin/Super Admin (belum ditugaskan ke admin tertentu).
     */
    public function up(): void
    {
        Schema::create('cs_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_conversation_id')->constrained('cs_conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['cs_conversation_id', 'created_at']);
            $table->index(['receiver_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_messages');
    }
};
