<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add a generated column that is user_id when status='active', else NULL
        DB::statement(<<<'SQL'
            ALTER TABLE rfid_cards
            ADD COLUMN active_user_id BIGINT GENERATED ALWAYS AS (
                CASE WHEN status = 'active' THEN user_id ELSE NULL END
            ) VIRTUAL;
        SQL
        );

        // Add unique index on generated column to ensure at most one active card per user
        DB::statement('CREATE UNIQUE INDEX rfid_cards_active_user_id_unique ON rfid_cards (active_user_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX rfid_cards_active_user_id_unique ON rfid_cards');
        DB::statement('ALTER TABLE rfid_cards DROP COLUMN active_user_id');
    }
};
