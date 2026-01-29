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
    ];

    protected $casts = [
        'sss_updated_at' => 'datetime',
        'tin_updated_at' => 'datetime',
        'philhealth_updated_at' => 'datetime',
        'pagibig_updated_at' => 'datetime',

        'profile_picture_updated_at' => 'datetime',
        'birth_certificate_updated_at' => 'datetime',
        'resume_updated_at' => 'datetime',
        'contract_updated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
