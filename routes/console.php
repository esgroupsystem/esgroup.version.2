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
| CROSSCHEX SYNC (every 5 minutes)
|--------------------------------------------------------------------------
*/
Schedule::command('crosschex:sync')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/crosschex-sync.log'))
    ->onSuccess(function (Stringable $output) {
        Log::info('crosschex:sync success', [
            'time' => now()->toDateTimeString(),
            'output' => (string) $output,
        ]);
    })
    ->onFailure(function (Stringable $output) {
        Log::error('crosschex:sync failed', [
            'time' => now()->toDateTimeString(),
            'output' => (string) $output,
        ]);
    });

/*
|--------------------------------------------------------------------------
| READY FOR DUTY (EVERY 5 MINUTES - ACTIVE MONITORING)
|--------------------------------------------------------------------------
*/

Schedule::command('leaves:employee-ready-for-duty')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/ready-duty-employee.log'))
    ->onSuccess(function (Stringable $output) {
        Log::info('employee ready-for-duty success', [
            'time' => now()->toDateTimeString(),
            'output' => (string) $output,
        ]);
    })
    ->onFailure(function (Stringable $output) {
        Log::error('employee ready-for-duty failed', [
            'time' => now()->toDateTimeString(),
            'output' => (string) $output,
        ]);
    });

Schedule::command('leaves:driver-ready-for-duty')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/ready-duty-driver.log'))
    ->onSuccess(function (Stringable $output) {
        Log::info('driver ready-for-duty success', [
            'time' => now()->toDateTimeString(),
            'output' => (string) $output,
        ]);
    })
    ->onFailure(function (Stringable $output) {
        Log::error('driver ready-for-duty failed', [
            'time' => now()->toDateTimeString(),
            'output' => (string) $output,
        ]);
    });

Schedule::command('leaves:conductor-ready-for-duty')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/ready-duty-conductor.log'))
    ->onSuccess(function (Stringable $output) {
        Log::info('conductor ready-for-duty success', [
            'time' => now()->toDateTimeString(),
            'output' => (string) $output,
        ]);
    })
    ->onFailure(function (Stringable $output) {
        Log::error('conductor ready-for-duty failed', [
            'time' => now()->toDateTimeString(),
            'output' => (string) $output,
        ]);
    });
