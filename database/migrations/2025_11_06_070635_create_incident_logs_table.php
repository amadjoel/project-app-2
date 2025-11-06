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
        Schema::create('incident_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->date('incident_date');
            $table->time('incident_time');
            $table->enum('severity', ['minor', 'moderate', 'serious', 'critical'])->default('minor');
            $table->enum('type', ['behavioral', 'academic', 'safety', 'health', 'bullying', 'other'])->default('behavioral');
            $table->string('title');
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('parent_notified_at')->nullable();
            $table->text('parent_response')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            // Indexes for quick lookups
            $table->index(['student_id', 'incident_date']);
            $table->index(['teacher_id', 'incident_date']);
            $table->index('severity');
            $table->index('resolved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_logs');
    }
};
