<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('behavior_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->time('time');
            $table->enum('type', ['positive', 'negative', 'neutral'])->default('neutral');
            $table->enum('category', [
                'participation',
                'cooperation',
                'respect',
                'responsibility',
                'leadership',
                'conflict',
                'disruption',
                'rule_violation',
                'other'
            ]);
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('points')->default(0)->comment('Positive or negative points');
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('parent_notified_at')->nullable();
            $table->text('parent_response')->nullable();
            $table->boolean('requires_followup')->default(false);
            $table->text('followup_notes')->nullable();
            $table->timestamp('followup_completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'date']);
            $table->index(['teacher_id', 'date']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('behavior_records');
    }
};
