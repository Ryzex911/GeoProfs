<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $table = 'leave_request';

    protected $fillable = [
        'employee_id',
        'manager_id',
        'type',
        'reason',
        'start_date',
        'end_date',
        'status',
        'submitted_at',
        'approved_at',
        'canceled_at',
        'notification_sent',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

}
