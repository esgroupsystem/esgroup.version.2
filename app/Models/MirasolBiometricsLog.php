<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MirasolBiometricsLog extends Model
{
    protected $fillable = [
        'crosschex_id',
        'employee_id',
        'employee_no',
        'employee_name',
        'check_time',
        'device_sn',
        'device_name',
        'state',
        'raw',
    ];

    protected $casts = [
        'check_time' => 'datetime',
        'raw' => 'array',
    ];
}
