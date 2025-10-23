<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $table = 'login_attempts';

    // Het is false omdat er geen created_at/updated_at kolommen hebben in die tabel.
    public $timestamps = false;

    public $fillable = [
        'user_id',
        'email_tried',
        'attempts',
        'attempted_at',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
    ];
}
