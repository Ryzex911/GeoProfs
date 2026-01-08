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

    // Datums en booleans automatisch omzetten
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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

    // De medewerker die het verlof heeft aangevraagd
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // Het type verlof (vakantie, ziek, etc.)
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    // De gebruiker die het verlof heeft goedgekeurd
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
