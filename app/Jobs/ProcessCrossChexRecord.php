<?php

namespace App\Jobs;

use App\Models\MirasolBiometricsLog;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCrossChexRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public array $record
    ) {
        $this->onQueue('crosschex');
    }

    public function handle(): void
    {
        $employee = data_get($this->record, 'employee', []);
        $device = data_get($this->record, 'device', []);

        $employeeNo =
            data_get($employee, 'serial_number')
            ?? data_get($employee, 'workno')
            ?? data_get($employee, 'employee_no')
            ?? data_get($employee, 'employeeNo');

        $employeeName =
            data_get($employee, 'name')
            ?? trim((string) data_get($employee, 'first_name').' '.(string) data_get($employee, 'last_name'));

        $checkTime =
            data_get($this->record, 'checktime')
            ?? data_get($this->record, 'check_time')
            ?? data_get($this->record, 'time');

        $deviceSn =
            data_get($device, 'serial_number')
            ?? data_get($device, 'sn')
            ?? data_get($this->record, 'device_sn');

        // Robust state mapping with fallback
        $state =
            data_get($this->record, 'state')
            ?? data_get($this->record, 'checktype')
            ?? data_get($this->record, 'check_type')
            ?? data_get($this->record, 'verifycode')
            ?? 'unknown';

        $crosschexId =
            data_get($this->record, 'uid')
            ?? data_get($this->record, 'id')
            ?? md5(($employeeNo ?? 'unknown').'|'.($checkTime ?? now()).'|'.($deviceSn ?? 'device').'|'.($state ?? 'state'));

        MirasolBiometricsLog::updateOrCreate(
            [
                'crosschex_id' => $crosschexId,
            ],
            [
                'employee_id' => data_get($employee, 'id') ?? data_get($employee, 'uid'),
                'employee_no' => $employeeNo,
                'employee_name' => $employeeName ?: null,
                'check_time' => $checkTime ? Carbon::parse($checkTime) : now(),
                'device_sn' => $deviceSn,
                'device_name' => data_get($device, 'name') ?? data_get($this->record, 'device_name'),
                'state' => $state, // Safe fallback
                'raw' => $this->record,
            ]
        );
    }
}
