<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword; // Voor wachtwoord reset

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'auth_code',
    ];

    // Mogelijke mutator die wachtwoord automatisch bcrypt
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            // Genereer een random 6-cijferige code
            $user->auth_code = rand(1000, 9999);
        });
    }


}
