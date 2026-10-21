<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('management_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kos_id')->constrained('kos')->cascadeOnDelete();
            $table->foreignId('property_owner_id')->constrained('property_owners')->restrictOnDelete();
            $table->enum('commission_type', ['percent', 'fixed'])->default('percent');
            $table->decimal('commission_value', 12, 2)->default(0);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['kos_id', 'effective_from']);
            $table->index(['kos_id', 'effective_to']);
            $table->unique(['kos_id', 'effective_from'], 'management_agreements_kos_from_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('management_agreements');
    }
};
