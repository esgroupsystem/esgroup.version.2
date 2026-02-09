<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Jobs\CrossChexSyncLogsJob;
use App\Models\Employee;
use App\Models\MirasolBiometricsLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MirasolBiometricsLogController extends Controller
{
    public function index(Request $request)
    {
        // ✅ Show table ONLY if search inputs were used
        $isSearch = $request->filled('q') || $request->filled('date_from') || $request->filled('date_to');

        $employees = Employee::select('id', 'full_name')->orderBy('full_name')->get();

        // ✅ Default empty paginator (no results)
        $rows = MirasolBiometricsLog::query()->whereRaw('1=0')->paginate(20);

        if ($isSearch) {
            $q = MirasolBiometricsLog::query();

            if ($request->filled('q')) {
                $s = trim((string) $request->q);
                $q->where(function ($x) use ($s) {
                    $x->where('employee_name', 'like', "%{$s}%")
                        ->orWhere('employee_no', 'like', "%{$s}%")
                        ->orWhere('device_sn', 'like', "%{$s}%");
                });
            }

            if ($request->filled('date_from')) {
                $from = Carbon::parse($request->date_from)->startOfDay();
                $q->where('check_time', '>=', $from);
            }

            if ($request->filled('date_to')) {
                $to = Carbon::parse($request->date_to)->endOfDay();
                $q->where('check_time', '<=', $to);
            }

            // ✅ Summary per employee per day: first + last record
            $rows = $q->selectRaw('
                employee_no,
                employee_name,
                DATE(check_time) as log_date,
                MIN(check_time) as time_in,
                MAX(check_time) as time_out
            ')
                ->whereNotNull('employee_no')
                ->whereNotNull('check_time')
                ->groupBy('employee_no', 'employee_name', 'log_date')
                ->orderByDesc('log_date')
                ->paginate(20)
                ->withQueryString();
        }

        // Keep $all if your blade uses it; make it empty safe
        $all = collect();

        return view('hr_department.mirasol_logs.index', compact('rows', 'all', 'employees', 'isSearch'));
    }

    public function startSync(Request $request)
    {
        try {
            $validated = $request->validate([
                'from' => ['required', 'date'],
                'to' => ['required', 'date', 'after_or_equal:from'],
            ]);

            $fromDate = Carbon::parse($validated['from'])->startOfDay();
            $toDate = Carbon::parse($validated['to'])->endOfDay();

            $jobId = (string) Str::uuid();
            $key = "crosschex_sync_status:{$jobId}";

            Cache::put($key, [
                'state' => 'queued',
                'message' => 'Queued...',
                'from' => $fromDate->toDateTimeString(),
                'to' => $toDate->toDateTimeString(),
                'page' => 0,
                'pageCount' => null,
                'saved' => 0,
                'percent' => 0,
                'done' => false,
                'error' => null,
            ], now()->addMinutes(60));

            CrossChexSyncLogsJob::dispatch($jobId, $fromDate->toDateTimeString(), $toDate->toDateTimeString());

            return response()->json([
                'ok' => true,
                'jobId' => $jobId,
                'date_from' => $fromDate->toDateString(),
                'date_to' => $toDate->toDateString(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('startSync failed', ['error' => $e->getMessage()]);

            return response()->json([
                'ok' => false,
                'message' => 'Failed to start sync. Check laravel.log.',
            ], 500);
        }
    }

    public function syncStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'job' => ['required', 'string'],
            ]);

            $key = "crosschex_sync_status:{$validated['job']}";
            $status = Cache::get($key);

            if (! $status) {
                return response()->json([
                    'ok' => false,
                    'state' => 'unknown',
                    'message' => 'No status found (maybe expired).',
                ], 404);
            }

            return response()->json(['ok' => true] + $status);
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('syncStatus failed', ['error' => $e->getMessage()]);

            return response()->json([
                'ok' => false,
                'message' => 'Failed to read status. Check laravel.log.',
            ], 500);
        }
    }
}
