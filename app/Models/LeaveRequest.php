<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
use HasFactory;

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
    'canceled_at',
    'notification_sent',
];

// Casts zodat we makkelijk met datums / booleans kunnen werken
protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'submitted_at' => 'datetime',
    'approved_at' => 'datetime',
    'canceled_at' => 'datetime',
    'notification_sent' => 'boolean',
];

    public function user()
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
}
