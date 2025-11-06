<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    // Using Spatie's HasRoles trait for role management

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function students()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id');
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id');
    }

    public function rfidCards()
    {
        return $this->hasMany(RFIDCard::class);
    }

    public function activeRFIDCard()
    {
        return $this->hasOne(RFIDCard::class)->where('status', 'active');
    }

    /**
     * The guardian (parent) for this student (if set).
     */
    public function guardian()
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    /**
     * Guardian's primary children (users whose guardian_id points to this user).
     */
    public function guardianChildren()
    {
        return $this->hasMany(User::class, 'guardian_id');
    }

    /**
     * Attendance records as a student.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    /**
     * Attendance records created as a teacher.
     */
    public function teacherAttendances()
    {
        return $this->hasMany(Attendance::class, 'teacher_id');
    }
}
