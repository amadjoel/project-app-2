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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('primary_parent_id')->nullable()->constrained('users')->nullOnDelete()->after('id');
        });

        // Populate primary_parent_id for students that have entries in parent_student pivot.
        // We'll pick the lowest parent_id for determinism.
        DB::statement(<<<'SQL'
            UPDATE users u
            JOIN (
                SELECT student_id, MIN(parent_id) AS parent_id
                FROM parent_student
                GROUP BY student_id
            ) ps ON ps.student_id = u.id
            SET u.primary_parent_id = ps.parent_id
            WHERE u.id IN (SELECT student_id FROM parent_student);
        SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('primary_parent_id');
        });
    }
};
