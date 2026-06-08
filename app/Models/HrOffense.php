<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrOffense extends Model
{
    protected $fillable = [
        'employee_id',
        'section',
        'offense_description',
        'offense_type',
        'offense_gravity',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
