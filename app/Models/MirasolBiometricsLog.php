<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $biometric_employee_id
 * @property string|null $crosschex_account
 * @property string|null $crosschex_account_name
 * @property string|null $crosschex_id
 * @property string|null $source_employee_id
 * @property string|null $employee_id
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property \Carbon\CarbonInterface|null $check_time
 * @property string|null $device_sn
 * @property string|null $device_name
 * @property string|null $state
 * @property array<string, mixed>|null $raw
 */
class MirasolBiometricsLog extends Model
{
    protected $fillable = [
        'crosschex_account',
        'crosschex_account_name',
        'crosschex_id',
        'source_employee_id',
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
