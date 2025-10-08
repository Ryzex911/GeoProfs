<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// Geeft de mogelijkheid om een query terug te geven
use Illuminate\Database\Query\Builder;

class TwoFactorAuth extends Model
{
    // Hier wordt de tabel gelinkt aan de modal
    protected $table = 'two_factor_auth';

    // Alleen deze velden kunnen gevuld worden bij het aanmaken of wijzigen
    protected $fillable = [
        'user_id',
        'channel',
        'code',
        'expires_at',
        'verified_at',
        'used_at'
    ];

    // De 2fa code wordt gehashed bij het retourneren van JSON of array
    protected $hidden = ['code'];

    // Behandel deze velden als echte datum/tijd i.p.v. string (Carbon library)
    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    // Elke 2fa record hoort bij 1 User.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Checkt of de 2fa code nog geldig is.
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('used_at')->where('expires_at', '>', now());
    }

    // Checkt of de 2fa code verlopen is.
    public function isExpired(): bool
    {
        // Controleer of 'expires_at' een waarde heeft en of deze tijd al voorbij is.
        // Als dat zo is → code is verlopen (true).
        // Als 'expires_at' leeg is of nog in de toekomst ligt → code is nog geldig (false).
        return $this->expires_at ? $this->expires_at->isPast() : false;
    }
}
