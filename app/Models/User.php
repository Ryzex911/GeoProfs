<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'lock_at',
        'role_id',
    ];

    //  Single source of truth: this auto-hashes on set
    protected $casts = [
        'password' => 'hashed',
        'lock_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            // Genereer een random 6 nummerige code
            $user->auth_code = rand(1000, 9999);
        });
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function approvedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    public function isLocked(): bool
    {
        return !is_null($this->lock_at);
    }

    public function lockNow(): void
    {
        $this->lock_at = now();
        $this->save();
    }

    public function unlock(): void
    {
        $this->lock_at = null;
        $this->save();
    }
}
