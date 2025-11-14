<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAsset extends Model
{
    protected $fillable = [
        'employee_id',
        'profile_picture',
        'sss_number',
        'tin_number',
        'philhealth_number',
        'pagibig_number',
        'birth_certificate',
        'resume',
        'contract',
        'date_hired',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
