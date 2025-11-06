<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthorizedPickup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'student_id',
        'rfid_card_id',
        'allowed',
        'notes',
    ];

    protected $casts = [
        'allowed' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function rfidCard()
    {
        return $this->belongsTo(RFIDCard::class, 'rfid_card_id');
    }
}
