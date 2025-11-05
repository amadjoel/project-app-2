<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RFIDCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rfid_cards';

    protected $fillable = [
        'card_number',
        'user_id',
        'status',
        'replaced_by_card_id',
        'deactivated_at',
        'deactivation_reason',
    ];

    protected $casts = [
        'deactivated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replacementCard(): BelongsTo
    {
        return $this->belongsTo(RFIDCard::class, 'replaced_by_card_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }

    public function scopeReplaced($query)
    {
        return $query->where('status', 'replaced');
    }
}
