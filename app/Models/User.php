<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'role_id',
    ];

    //  Single source of truth: this auto-hashes on set
    protected $casts = [
        'password' => 'hashed',
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
}
