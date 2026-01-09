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

    protected $casts = [
        'start_date' => 'datetime',   // ✅ was 'date'
        'end_date' => 'datetime',     // ✅ was 'date'
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'canceled_at' => 'datetime',
        'notification_sent' => 'boolean',
    ];

    // Status constants
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELED = 'canceled';

    // Relaties
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

    // Scope voor pending requests
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // Annuleren
    public function cancel()
    {
        $this->status = self::STATUS_CANCELED;
        $this->canceled_at = now();
        $this->save();
    }
}
