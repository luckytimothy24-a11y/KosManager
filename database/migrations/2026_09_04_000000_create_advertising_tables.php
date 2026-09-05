<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Domain advertising/monetisasi KosManager.
     *
     * Entity terpisah dari domain pembayaran sewa tenant (pembayarans) agar
     * aliran revenue advertising tidak tercampur dengan pembayaran sewa.
     */
    public function up(): void
    {
        Schema::create('advertising_packages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('duration_days');
            // Kemampuan paket (satu paket dapat memiliki beberapa jenis promosi).
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->boolean('is_homepage')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('advertising_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_number', 20)->unique();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kos_id')->constrained('kos')->onDelete('cascade');
            $table->foreignId('package_id')->constrained('advertising_packages')->restrictOnDelete();
            $table->enum('status', [
                'draft',
                'pending_payment',
                'paid',
                'pending_review',
                'approved',
                'active',
                'completed',
                'rejected',
                'suspended',
                'cancelled',
            ])->default('draft');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->decimal('budget', 12, 2)->default(0);
            // Snapshot kemampuan paket saat campaign dibuat (agar tetap aman walau paket diedit).
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->boolean('is_homepage')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'starts_at', 'ends_at']);
            $table->index('kos_id');
            $table->index('package_id');
        });

        Schema::create('advertising_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->foreignId('campaign_id')->constrained('advertising_campaigns')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->index('owner_id');
        });

        Schema::create('advertising_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('advertising_campaigns')->onDelete('cascade');
            $table->enum('type', ['impression', 'click', 'conversion'])->default('impression');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('session_key')->nullable();
            $table->string('placement', 50)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['campaign_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertising_events');
        Schema::dropIfExists('advertising_orders');
        Schema::dropIfExists('advertising_campaigns');
        Schema::dropIfExists('advertising_packages');
    }
};
