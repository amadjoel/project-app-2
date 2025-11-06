<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BehaviorRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'date',
        'time',
        'type',
        'category',
        'title',
        'description',
        'points',
        'parent_notified',
        'parent_notified_at',
        'parent_response',
        'requires_followup',
        'followup_notes',
        'followup_completed_at',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime',
        'parent_notified' => 'boolean',
        'parent_notified_at' => 'datetime',
        'requires_followup' => 'boolean',
        'followup_completed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'positive' => 'success',
            'negative' => 'danger',
            'neutral' => 'gray',
            default => 'gray',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'participation' => 'Participation',
            'cooperation' => 'Cooperation',
            'respect' => 'Respect',
            'responsibility' => 'Responsibility',
            'leadership' => 'Leadership',
            'conflict' => 'Conflict',
            'disruption' => 'Disruption',
            'rule_violation' => 'Rule Violation',
            'other' => 'Other',
            default => $this->category,
        };
    }
}
