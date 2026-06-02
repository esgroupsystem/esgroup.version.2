<?php

namespace App\Http\Controllers;

use App\Models\MirasolBiometricsLog;
use Illuminate\Http\Request;

class BiometricsViewController extends Controller
{
    public function index(Request $request)
    {
        $query = MirasolBiometricsLog::query();

        if ($request->filled('q')) {
            $search = $request->q;

            $query->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('check_time', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('check_time', '<=', $request->to);
        }

        $logs = $query
            ->latest('check_time')
            ->paginate(10)
            ->withQueryString();

        $totalLogs = MirasolBiometricsLog::count();

        $todayLogs = MirasolBiometricsLog::whereDate('check_time', now()->toDateString())
            ->count();

        $deviceCount = MirasolBiometricsLog::whereNotNull('device_sn')
            ->distinct('device_sn')
            ->count('device_sn');

        $uniqueEmployees = MirasolBiometricsLog::whereNotNull('employee_no')
            ->distinct('employee_no')
            ->count('employee_no');

        return view('biometrics.logs', compact(
            'logs',
            'totalLogs',
            'todayLogs',
            'deviceCount',
            'uniqueEmployees'
        ));
    }

    public function latest()
    {
        $logs = MirasolBiometricsLog::latest('check_time')
            ->take(10)
            ->get();

        return response()->json([
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'employee_no' => $log->employee_no,
                    'employee_name' => $log->employee_name,
                    'check_time' => optional($log->check_time)->format('Y-m-d H:i:s'),
                    'device_name' => $log->device_name,
                    'device_sn' => $log->device_sn,
                    'state' => $log->state,
                ];
            }),
        ]);
    }
}
