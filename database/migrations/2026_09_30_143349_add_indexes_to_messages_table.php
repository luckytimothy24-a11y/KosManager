<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Index messages sudah dibuat di 2026_09_10_000000_create_messages_table
     * (kos_id+sender_id+receiver_id dan receiver_id+read_at).
     * Migration ini dijadikan no-op agar tidak duplikat / merusak migrate:fresh.
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
