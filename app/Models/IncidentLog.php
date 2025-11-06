<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'incident_date',
        'incident_time',
        'severity',
        'type',
        'title',
        'description',
        'action_taken',
        'parent_notified',
        'parent_notified_at',
        'parent_response',
        'resolved',
        'resolved_at',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'incident_time' => 'datetime:H:i',
        'parent_notified' => 'boolean',
        'parent_notified_at' => 'datetime',
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
