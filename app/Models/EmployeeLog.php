<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLog extends Model
{
    protected $fillable = ['employee_id', 'action', 'meta', 'user_id'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
