<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Percakapan Customer Service (tenant ↔ Admin/Super Admin).
     *
     * Sengaja dipisahkan dari tabel `messages` (tenant ↔ owner) agar
     * business logic, status, dan autorisasi CS tidak tercampur.
     */
    public function up(): void
    {
        Schema::create('cs_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('open')->comment('open/in_progress/resolved');
            $table->string('subject')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['assigned_admin_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_conversations');
    }
};
