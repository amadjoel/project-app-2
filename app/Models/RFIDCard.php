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

    protected static function booted()
    {
        // When creating a new card, default to inactive if no user assigned
        static::creating(function ($card) {
            if (empty($card->user_id) && empty($card->status)) {
                $card->status = 'inactive';
            } elseif (!empty($card->user_id) && empty($card->status)) {
                $card->status = 'active';
            }
        });

        // When updating a card, set to active if user assigned, inactive if unassigned
        static::updating(function ($card) {
            if ($card->isDirty('user_id')) {
                if (empty($card->user_id)) {
                    $card->status = 'inactive';
                    $card->deactivated_at = now();
                    $card->deactivation_reason = 'Unassigned from user';
                } elseif ($card->status === 'inactive' && !empty($card->user_id)) {
                    $card->status = 'active';
                    $card->deactivated_at = null;
                    $card->deactivation_reason = null;
                }
            }
        });
    }

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
