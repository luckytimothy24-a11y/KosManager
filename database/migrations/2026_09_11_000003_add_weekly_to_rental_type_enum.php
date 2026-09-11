<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan 'weekly' ke enum rental_type pada tabel bookings dan kontraks.
     * Rebuild tabel untuk mendukung both MySQL dan SQLite.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // ── bookings ──
        DB::statement('CREATE TABLE "bookings_weekly_upgrade" ("id" integer primary key autoincrement, "booking_code" varchar(20) unique, "user_id" integer references "users"("id") on delete cascade, "kos_id" integer references "kos"("id") on delete cascade, "kamar_id" integer references "kamar"("id") on delete cascade, "booking_date" date, "start_date" date, "end_date" date, "rental_type" varchar not null default \'monthly\', "price" decimal(12,2), "status" varchar not null default \'pending\', "notes" text, "identity_confirmed_at" datetime, "identity_number" varchar(50), "phone" varchar(20), "address" text, "identity_data_submitted_at" datetime, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime)');

        DB::statement(
            'INSERT INTO "bookings_weekly_upgrade" ("id", "booking_code", "user_id", "kos_id", "kamar_id", "booking_date", "start_date", "end_date", "rental_type", "price", "status", "notes", "identity_confirmed_at", "identity_number", "phone", "address", "identity_data_submitted_at", "created_at", "updated_at", "deleted_at")
             SELECT "id", "booking_code", "user_id", "kos_id", "kamar_id", "booking_date", "start_date", "end_date", "rental_type", "price", "status", "notes", "identity_confirmed_at", "identity_number", "phone", "address", "identity_data_submitted_at", "created_at", "updated_at", "deleted_at" FROM "bookings"'
        );

        DB::statement('DROP TABLE "bookings"');
        DB::statement('ALTER TABLE "bookings_weekly_upgrade" RENAME TO "bookings"');

        // ── kontraks ──
        DB::statement('CREATE TABLE "kontraks_weekly_upgrade" ("id" integer primary key autoincrement, "contract_number" varchar(20) unique, "penghuni_id" integer references "penghunis"("id") on delete cascade, "kos_id" integer references "kos"("id") on delete cascade, "kamar_id" integer references "kamar"("id") on delete cascade, "rental_type" varchar not null default \'monthly\', "rental_price" decimal(12,2), "start_date" date, "end_date" date, "status" varchar not null default \'active\', "notes" text, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime)');

        DB::statement(
            'INSERT INTO "kontraks_weekly_upgrade" ("id", "contract_number", "penghuni_id", "kos_id", "kamar_id", "rental_type", "rental_price", "start_date", "end_date", "status", "notes", "created_at", "updated_at", "deleted_at")
             SELECT "id", "contract_number", "penghuni_id", "kos_id", "kamar_id", "rental_type", "rental_price", "start_date", "end_date", "status", "notes", "created_at", "updated_at", "deleted_at" FROM "kontraks"'
        );

        DB::statement('DROP TABLE "kontraks"');
        DB::statement('ALTER TABLE "kontraks_weekly_upgrade" RENAME TO "kontraks"');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        // ── bookings ──
        DB::statement('CREATE TABLE "bookings_downgrade" ("id" integer primary key autoincrement, "booking_code" varchar(20) unique, "user_id" integer references "users"("id") on delete cascade, "kos_id" integer references "kos"("id") on delete cascade, "kamar_id" integer references "kamar"("id") on delete cascade, "booking_date" date, "start_date" date, "end_date" date, "rental_type" varchar not null default \'monthly\', "price" decimal(12,2), "status" varchar not null default \'pending\', "notes" text, "identity_confirmed_at" datetime, "identity_number" varchar(50), "phone" varchar(20), "address" text, "identity_data_submitted_at" datetime, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime)');

        DB::statement(
            "INSERT INTO \"bookings_downgrade\" (\"id\", \"booking_code\", \"user_id\", \"kos_id\", \"kamar_id\", \"booking_date\", \"start_date\", \"end_date\", \"rental_type\", \"price\", \"status\", \"notes\", \"identity_confirmed_at\", \"identity_number\", \"phone\", \"address\", \"identity_data_submitted_at\", \"created_at\", \"updated_at\", \"deleted_at\")
             SELECT \"id\", \"booking_code\", \"user_id\", \"kos_id\", \"kamar_id\", \"booking_date\", \"start_date\", \"end_date\",
                    CASE WHEN \"rental_type\" = 'weekly' THEN 'monthly' ELSE \"rental_type\" END,
                    \"price\", \"status\", \"notes\", \"identity_confirmed_at\", \"identity_number\", \"phone\", \"address\", \"identity_data_submitted_at\", \"created_at\", \"updated_at\", \"deleted_at\" FROM \"bookings\""
        );

        DB::statement('DROP TABLE "bookings"');
        DB::statement('ALTER TABLE "bookings_downgrade" RENAME TO "bookings"');

        // ── kontraks ──
        DB::statement('CREATE TABLE "kontraks_downgrade" ("id" integer primary key autoincrement, "contract_number" varchar(20) unique, "penghuni_id" integer references "penghunis"("id") on delete cascade, "kos_id" integer references "kos"("id") on delete cascade, "kamar_id" integer references "kamar"("id") on delete cascade, "rental_type" varchar not null default \'monthly\', "rental_price" decimal(12,2), "start_date" date, "end_date" date, "status" varchar not null default \'active\', "notes" text, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime)');

        DB::statement(
            "INSERT INTO \"kontraks_downgrade\" (\"id\", \"contract_number\", \"penghuni_id\", \"kos_id\", \"kamar_id\", \"rental_type\", \"rental_price\", \"start_date\", \"end_date\", \"status\", \"notes\", \"created_at\", \"updated_at\", \"deleted_at\")
             SELECT \"id\", \"contract_number\", \"penghuni_id\", \"kos_id\", \"kamar_id\",
                    CASE WHEN \"rental_type\" = 'weekly' THEN 'monthly' ELSE \"rental_type\" END,
                    \"rental_price\", \"start_date\", \"end_date\", \"status\", \"notes\", \"created_at\", \"updated_at\", \"deleted_at\" FROM \"kontraks\""
        );

        DB::statement('DROP TABLE "kontraks"');
        DB::statement('ALTER TABLE "kontraks_downgrade" RENAME TO "kontraks"');

        Schema::enableForeignKeyConstraints();
    }
};
