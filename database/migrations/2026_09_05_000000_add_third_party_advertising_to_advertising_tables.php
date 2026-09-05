<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perluas domain advertising agar mendukung ADVERTISER PIHAK KETIGA.
     *
     * Sebelumnya setiap campaign WAJIB memiliki kos_id (model "atau owner
     * membayar -> kosnya menjadi iklan"). Kini campaign boleh dimiliki oleh
     * advertiser pihak ketiga (tidak wajib kos_id) dengan identitas advertiser,
     * creative, CTA, destination URL, dan placement yang jelas.
     *
     * Data lama TIDAK dihapus: kolom kos_id dijadikan nullable dan seluruh baris
     * yang sudah ada dipertahankan apa adanya.
     *
     * SQLite tidak mendukung ALTER untuk membuat kolom NOT NULL menjadi NULL,
     * sehingga dipakai pola rebuild tabel (sama seperti migrasi bookings expired).
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('advertising_campaigns_new', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_number', 20)->unique();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            // kos_id kini OPSIONAL: campaign pihak ketiga tidak terkait kos mana pun.
            $table->foreignId('kos_id')->nullable()->constrained('kos')->onDelete('cascade');
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
            // Identitas advertiser pihak ketiga (kosong untuk campaign promosi kos owner).
            $table->string('advertiser_name', 120)->nullable();
            $table->string('advertiser_logo', 255)->nullable();
            $table->text('advertiser_description')->nullable();
            // Creative & destination kampanye pihak ketiga.
            $table->string('headline', 190)->nullable();
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('cta_label', 60)->nullable();
            $table->string('destination_url', 500)->nullable();
            // Placement: homepage | marketplace | detail | native
            $table->string('placement', 50)->nullable()->index();
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

        DB::statement(
            'INSERT INTO advertising_campaigns_new
             (id, campaign_number, owner_id, kos_id, package_id, status, starts_at, ends_at, budget,
              is_featured, is_sponsored, is_homepage,
              advertiser_name, advertiser_logo, advertiser_description, headline, description, image,
              cta_label, destination_url, placement,
              approved_by, approved_at, rejection_reason, suspension_reason, created_at, updated_at, deleted_at)
             SELECT id, campaign_number, owner_id, kos_id, package_id, status, starts_at, ends_at, budget,
                    is_featured, is_sponsored, is_homepage,
                    NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
                    approved_by, approved_at, rejection_reason, suspension_reason, created_at, updated_at, deleted_at
             FROM advertising_campaigns'
        );

        Schema::dropIfExists('advertising_campaigns');
        Schema::rename('advertising_campaigns_new', 'advertising_campaigns');

        Schema::table('advertising_packages', function (Blueprint $table) {
            $table->string('placement', 50)->nullable()->after('sort_order');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('advertising_packages', function (Blueprint $table) {
            $table->dropColumn('placement');
        });

        Schema::create('advertising_campaigns_old', function (Blueprint $table) {
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

        DB::statement(
            'INSERT INTO advertising_campaigns_old
             (id, campaign_number, owner_id, kos_id, package_id, status, starts_at, ends_at, budget,
              is_featured, is_sponsored, is_homepage,
              approved_by, approved_at, rejection_reason, suspension_reason, created_at, updated_at, deleted_at)
             SELECT id, campaign_number, owner_id, kos_id, package_id, status, starts_at, ends_at, budget,
                    is_featured, is_sponsored, is_homepage,
                    approved_by, approved_at, rejection_reason, suspension_reason, created_at, updated_at, deleted_at
             FROM advertising_campaigns'
        );

        Schema::dropIfExists('advertising_campaigns');
        Schema::rename('advertising_campaigns_old', 'advertising_campaigns');

        Schema::enableForeignKeyConstraints();
    }
};
