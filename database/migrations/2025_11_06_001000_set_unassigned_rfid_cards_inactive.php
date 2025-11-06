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
        // Set all unassigned RFID cards to inactive
        DB::table('rfid_cards')
            ->whereNull('user_id')
            ->where('status', 'active')
            ->update([
                'status' => 'inactive',
                'deactivated_at' => now(),
                'deactivation_reason' => 'Unassigned card - auto-deactivated',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally revert - set unassigned cards back to active
        DB::table('rfid_cards')
            ->whereNull('user_id')
            ->where('status', 'inactive')
            ->where('deactivation_reason', 'Unassigned card - auto-deactivated')
            ->update([
                'status' => 'active',
                'deactivated_at' => null,
                'deactivation_reason' => null,
                'updated_at' => now(),
            ]);
    }
};
