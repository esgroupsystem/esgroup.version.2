<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    protected $fillable = [
        'employee_id',
        'claim_type',
        'status',
        'reference_no',
        'date_of_notification',
        'date_filed',
        'approval_date',
        'fund_request_date',
        'fund_released_date',
        'amount',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_notification' => 'date',
        'date_filed' => 'date',
        'approval_date' => 'date',
        'fund_request_date' => 'date',
        'fund_released_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
