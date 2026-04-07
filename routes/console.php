<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Stringable;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| SHARED SCHEDULE LOGGER
|--------------------------------------------------------------------------
*/
$logScheduledCommand = function (
    string $commandName,
    string $successLabel,
    string $failureLabel
) {
    return [
        'success' => function (Stringable $output) use ($commandName, $successLabel) {
            $text = trim((string) $output);

            Log::info($successLabel, [
                'command' => $commandName,
                'time' => now()->toDateTimeString(),
                'has_output' => $text !== '',
                'output_preview' => $text !== '' ? mb_substr($text, 0, 1000) : null,
            ]);
        },
        'failure' => function (Stringable $output) use ($commandName, $failureLabel) {
            $text = trim((string) $output);

            Log::error($failureLabel, [
                'command' => $commandName,
                'time' => now()->toDateTimeString(),
                'has_output' => $text !== '',
                'output_preview' => $text !== '' ? mb_substr($text, 0, 2000) : null,
            ]);
        },
    ];
};

/*
|--------------------------------------------------------------------------
| CROSSCHEX SYNC
|--------------------------------------------------------------------------
| Keep every 5 minutes if really needed.
| Added:
| - runInBackground()
| - lock timeout
| - lighter logging
|--------------------------------------------------------------------------
*/
$crosschexLogger = $logScheduledCommand(
    'crosschex:sync',
    'crosschex:sync success',
    'crosschex:sync failed'
);

Schedule::command('crosschex:sync')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->runInBackground()
    ->sendOutputTo(storage_path('logs/crosschex-sync.log'))
    ->onSuccess($crosschexLogger['success'])
    ->onFailure($crosschexLogger['failure']);

/*
|--------------------------------------------------------------------------
| READY FOR DUTY
|--------------------------------------------------------------------------
| Changed to every 10 minutes to reduce load.
| If you truly need 5 minutes, change back.
|--------------------------------------------------------------------------
*/
$employeeLogger = $logScheduledCommand(
    'leaves:employee-ready-for-duty',
    'employee ready-for-duty success',
    'employee ready-for-duty failed'
);

Schedule::command('leaves:employee-ready-for-duty')
    ->everyTenMinutes()
    ->withoutOverlapping(10)
    ->runInBackground()
    ->sendOutputTo(storage_path('logs/ready-duty-employee.log'))
    ->onSuccess($employeeLogger['success'])
    ->onFailure($employeeLogger['failure']);

$driverLogger = $logScheduledCommand(
    'leaves:driver-ready-for-duty',
    'driver ready-for-duty success',
    'driver ready-for-duty failed'
);

Schedule::command('leaves:driver-ready-for-duty')
    ->everyTenMinutes()
    ->withoutOverlapping(10)
    ->runInBackground()
    ->sendOutputTo(storage_path('logs/ready-duty-driver.log'))
    ->onSuccess($driverLogger['success'])
    ->onFailure($driverLogger['failure']);

$conductorLogger = $logScheduledCommand(
    'leaves:conductor-ready-for-duty',
    'conductor ready-for-duty success',
    'conductor ready-for-duty failed'
);

Schedule::command('leaves:conductor-ready-for-duty')
    ->everyTenMinutes()
    ->withoutOverlapping(10)
    ->runInBackground()
    ->sendOutputTo(storage_path('logs/ready-duty-conductor.log'))
    ->onSuccess($conductorLogger['success'])
    ->onFailure($conductorLogger['failure']);
