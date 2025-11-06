<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new column guardian_id
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('guardian_id')->nullable()->after('id');
        });

        // Copy values from primary_parent_id to guardian_id
        DB::statement('UPDATE users SET guardian_id = primary_parent_id WHERE primary_parent_id IS NOT NULL');

        // Add foreign key constraint for guardian_id
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('guardian_id')->references('id')->on('users')->nullOnDelete();
        });

        // Drop the old constrained FK and column if exists
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'primary_parent_id')) {
                try {
                    $table->dropConstrainedForeignId('primary_parent_id');
                } catch (\Throwable $e) {
                    // If the constraint name differs or cannot be dropped via helper, drop column directly
                    if (Schema::hasColumn('users', 'primary_parent_id')) {
                        $table->dropColumn('primary_parent_id');
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate primary_parent_id and copy data back
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('primary_parent_id')->nullable()->after('id');
        });

        DB::statement('UPDATE users SET primary_parent_id = guardian_id WHERE guardian_id IS NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('primary_parent_id')->references('id')->on('users')->nullOnDelete();
        });

        // Drop guardian_id
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'guardian_id')) {
                try {
                    $table->dropConstrainedForeignId('guardian_id');
                } catch (\Throwable $e) {
                    if (Schema::hasColumn('users', 'guardian_id')) {
                        $table->dropColumn('guardian_id');
                    }
                }
            }
        });
    }
};
