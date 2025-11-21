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
    ];

    protected $casts = [
        'date_hired' => 'date',
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
}
