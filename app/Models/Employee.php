<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'full_name',
        'department_id',
        'position_id',
        'email',
        'phone_number',
        'company',
        'status',
        'date_hired',
        'garage',
        'date_of_birth',
        'address_1',
        'address_2',
        'emergency_name',
        'emergency_contact',
        'date_resigned',
        'last_duty',
        'clearance_date',
        'last_pay_status',
        'last_pay_date',
    ];

    protected $casts = [
        'date_hired' => 'date',
        'date_of_birth' => 'date',
        'date_resigned' => 'date',
        'last_duty' => 'date',
        'clearance_date' => 'date',
        'last_pay_date' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function asset()
    {
        return $this->hasOne(EmployeeAsset::class);
    }

    public function histories()
    {
        return $this->hasMany(EmployeeHistory::class);
    }

    public function attachments()
    {
        return $this->hasMany(EmployeeAttachment::class);
    }

    public function driverLeaves()
    {
        return $this->hasMany(DriverLeave::class, 'employee_id');
    }

    public function logs()
    {
        return $this->hasMany(\App\Models\EmployeeLog::class)->latest();
    }
}
