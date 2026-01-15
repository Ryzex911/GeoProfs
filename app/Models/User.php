<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Velden die mogen gevuld worden door laravel
    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'lock_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
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

    // Relationships
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    // Role helpers
    // Controleerd of een user een bepaalde role(en) heeft door true of false te geven.
    public function hasRole(string|array $roles): bool
    {
        // Rollen worden omgezet naar een array om makkelijker mee te werken.
        $roles = is_array($roles) ? $roles : [$roles];

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function activeRoleIs(string $roleName): bool
    {
        $roleId = session('active_role_id');
        if (!$roleId) return false;

        return $this->roles()
            ->where('roles.id', $roleId)
            ->where('roles.name', $roleName)
            ->exists();
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function approvedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    // Lock helpers
    // Controleerd of een user geblokeerd is
    public function isLocked(): bool
    {
        return !is_null($this->lock_at);
    }

    // Een functie om een user te blokkeren
    public function lockNow(): void
    {
        $this->lock_at = now();
        $this->save();
    }

    // Een functie om een user te deblokkeren
    public function unlock(): void
    {
        $this->lock_at = null;
        $this->save();
    }
}
