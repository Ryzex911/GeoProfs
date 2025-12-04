<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
use HasFactory;

protected $table = 'leave_requests';

protected $fillable = [
    'manager_id',
    'type',
    'reason',
    'status',
];

// Casts zodat we makkelijk met datums / booleans kunnen werken
protected $casts = [
    'start_date' => 'datetime',
    'end_date' => 'datetime',
    'submitted_at' => 'datetime',
    'approved_at' => 'datetime',
    'canceled_at' => 'datetime',
    'notification_sent' => 'boolean',
];

public function employee()
{
    return $this->belongsTo(User::class, 'employee_id');
}


}
