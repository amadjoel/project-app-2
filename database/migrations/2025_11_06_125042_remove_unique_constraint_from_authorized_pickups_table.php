<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the foreign keys first
        Schema::table('authorized_pickups', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['student_id']);
        });
        
        // Drop the unique constraint
        Schema::table('authorized_pickups', function (Blueprint $table) {
            $table->dropUnique('authorized_pickups_parent_id_student_id_unique');
        });
        
        // Re-add the foreign keys
        Schema::table('authorized_pickups', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('authorized_pickups', function (Blueprint $table) {
            $table->unique(['parent_id', 'student_id'], 'authorized_pickups_parent_id_student_id_unique');
        });
    }
};
