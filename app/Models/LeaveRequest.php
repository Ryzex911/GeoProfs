<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'leave_requests';

    // ✅ Status constants
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELED = 'canceled';

    // Velden die mass assignable zijn
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'reason',
        'start_date',
        'end_date',
        'proof',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by',
        'canceled_at',
        'notification_sent',
    ];

    // ✅ Datums en booleans automatisch omzetten
    protected $casts = [
        // jij werkt met tijden in UI + DB, dus datetime
        'start_date' => 'datetime',
        'end_date' => 'datetime',

        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'canceled_at' => 'datetime',
        'notification_sent' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaties
    |--------------------------------------------------------------------------
    */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (optioneel maar handig)
    |--------------------------------------------------------------------------
    */

    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELED,
            'canceled_at' => now(),
        ]);
    }

    public function approve(?int $approverId = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);
    }

    public function reject(?int $approverId = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);
    }
}
