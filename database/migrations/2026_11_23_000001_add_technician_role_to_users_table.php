<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan role 'technician' ke kolom users.role (additive, rollback-able).
     *
     * Cross-driver:
     * - SQLite: rebuild tabel (CHECK constraint tidak bisa di-ALTER).
     * - MySQL/MariaDB: ALTER TABLE ... MODIFY COLUMN.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $this->upSqlite();

            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','owner','pencari','technician') NOT NULL DEFAULT 'pencari'");

            return;
        }

        Schema::table('users', function ($table) {
            $table->enum('role', ['super_admin', 'admin', 'owner', 'pencari', 'technician'])->default('pencari')->change();
        });
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $this->downSqlite();

            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("UPDATE users SET role = 'pencari' WHERE role = 'technician'");
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','owner','pencari') NOT NULL DEFAULT 'pencari'");

            return;
        }
    }

    private function upSqlite(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::statement('CREATE TABLE "users_technician_upgrade" ("id" integer primary key autoincrement not null, "name" varchar not null, "email" varchar not null, "email_verified_at" datetime, "password" varchar not null, "remember_token" varchar, "two_factor_secret" text, "two_factor_recovery_codes" text, "two_factor_confirmed_at" datetime, "role" varchar check ("role" in (\'super_admin\', \'admin\', \'owner\', \'pencari\', \'technician\')) not null default \'pencari\', "phone" varchar, "address" text, "avatar" varchar, "is_active" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, "identity_number_encrypted" text, "identity_number_hash" varchar)');

        DB::statement(
            'INSERT INTO "users_technician_upgrade" ("id", "name", "email", "email_verified_at", "password", "remember_token", "two_factor_secret", "two_factor_recovery_codes", "two_factor_confirmed_at", "role", "phone", "address", "avatar", "is_active", "created_at", "updated_at", "deleted_at", "identity_number_encrypted", "identity_number_hash")
             SELECT "id", "name", "email", "email_verified_at", "password", "remember_token", "two_factor_secret", "two_factor_recovery_codes", "two_factor_confirmed_at", "role", "phone", "address", "avatar", "is_active", "created_at", "updated_at", "deleted_at", "identity_number_encrypted", "identity_number_hash" FROM "users"'
        );

        DB::statement('DROP TABLE "users"');
        DB::statement('ALTER TABLE "users_technician_upgrade" RENAME TO "users"');

        Schema::enableForeignKeyConstraints();
    }

    private function downSqlite(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::statement('CREATE TABLE "users_technician_downgrade" ("id" integer primary key autoincrement not null, "name" varchar not null, "email" varchar not null, "email_verified_at" datetime, "password" varchar not null, "remember_token" varchar, "two_factor_secret" text, "two_factor_recovery_codes" text, "two_factor_confirmed_at" datetime, "role" varchar check ("role" in (\'super_admin\', \'admin\', \'owner\', \'pencari\')) not null default \'pencari\', "phone" varchar, "address" text, "avatar" varchar, "is_active" tinyint(1) not null default \'1\', "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, "identity_number_encrypted" text, "identity_number_hash" varchar)');

        DB::statement(
            "INSERT INTO \"users_technician_downgrade\" (\"id\", \"name\", \"email\", \"email_verified_at\", \"password\", \"remember_token\", \"two_factor_secret\", \"two_factor_recovery_codes\", \"two_factor_confirmed_at\", \"role\", \"phone\", \"address\", \"avatar\", \"is_active\", \"created_at\", \"updated_at\", \"deleted_at\", \"identity_number_encrypted\", \"identity_number_hash\")
             SELECT \"id\", \"name\", \"email\", \"email_verified_at\", \"password\", \"remember_token\", \"two_factor_secret\", \"two_factor_recovery_codes\", \"two_factor_confirmed_at\",
                    CASE WHEN \"role\" = 'technician' THEN 'pencari' ELSE \"role\" END,
                    \"phone\", \"address\", \"avatar\", \"is_active\", \"created_at\", \"updated_at\", \"deleted_at\", \"identity_number_encrypted\", \"identity_number_hash\" FROM \"users\""
        );

        DB::statement('DROP TABLE "users"');
        DB::statement('ALTER TABLE "users_technician_downgrade" RENAME TO "users"');

        Schema::enableForeignKeyConstraints();
    }
};
