<?php

// app/Http/Controllers/BiometricsViewController.php

namespace App\Http\Controllers;

use App\Models\MirasolBiometricsLog;

class BiometricsViewController extends Controller
{
    public function index()
    {
        $logs = MirasolBiometricsLog::latest('check_time')->paginate(50);

        return view('biometrics.logs', compact('logs'));
    }

    // API endpoint for polling
    public function latest()
    {
        $logs = MirasolBiometricsLog::latest('check_time')->take(50)->get();

        return response()->json([
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'employee_no' => $log->employee_no,
                    'employee_name' => $log->employee_name,
                    'check_time' => $log->check_time->format('Y-m-d H:i:s'),
                    'device_name' => $log->device_name,
                    'device_sn' => $log->device_sn,
                    'state' => $log->state,
                ];
            }),
        ]);
    }
}
