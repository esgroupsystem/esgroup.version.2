<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeHistory extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'offense_id',
        'disciplinary_action',
        'sda_amount',
        'sda_terms',
        'sda_start_date',
        'sda_end_date',
        'description',
        'start_date',
        'end_date',
        'suspension_start_date',
        'suspension_end_date',
    ];

    protected $casts = [
        'disciplinary_action' => 'array',
        'sda_start_date' => 'date',
        'sda_end_date' => 'date',
        'suspension_start_date' => 'date',
        'suspension_end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function offense()
    {
        return $this->belongsTo(HrOffense::class, 'offense_id');
    }
}
