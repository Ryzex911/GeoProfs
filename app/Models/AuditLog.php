<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Schema;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    // Alleen created_at (geen updated_at)
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Niet aanpassen via Eloquent
        static::updating(fn () => false);

        // Niet verwijderen via Eloquent (retention doen we straks via DB::table)
        static::deleting(fn () => false);
    }

    /**
     * Relatie naar de gebruiker die de actie uitvoerde.
     * Ondersteunt zowel nieuw schema (actor_id) als oud schema (user_id).
     */
    public function user(): BelongsTo
    {
        if (Schema::hasColumn($this->getTable(), 'actor_id')) {
            return $this->belongsTo(User::class, 'actor_id');
        }

        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relatie naar het object waarop de actie is uitgevoerd (LeaveRequest, User, etc.)
     * Ondersteunt nieuw schema (auditable_type/auditable_id) én oud schema (entity/entity_id).
     */
    public function auditable(): MorphTo
    {
        // Nieuw schema
        if (Schema::hasColumn($this->getTable(), 'auditable_type') && Schema::hasColumn($this->getTable(), 'auditable_id')) {
            return $this->morphTo(__FUNCTION__, 'auditable_type', 'auditable_id');
        }

        // Oud schema fallback
        return $this->morphTo(__FUNCTION__, 'entity', 'entity_id');
    }
}
