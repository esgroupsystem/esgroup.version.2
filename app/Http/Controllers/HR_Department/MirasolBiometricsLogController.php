<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Jobs\CrossChexSyncLogsJob;
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
        $people = MirasolBiometricsLog::query()
            ->selectRaw('employee_no, MAX(employee_name) as employee_name')
            ->whereNotNull('employee_no')
            ->where('employee_no', '!=', '')
            ->groupBy('employee_no')
            ->orderBy('employee_name')
            ->get();

        $isSearch = ($request->filled('employee_no') || $request->filled('q'))
            && ($request->filled('date_from') || $request->filled('date_to'));

        $base = MirasolBiometricsLog::query()->whereNotNull('check_time');

        if ($request->filled('employee_no')) {
            $base->where('employee_no', $request->employee_no);
        } elseif ($request->filled('q')) {
            $s = trim((string) $request->q);
            $base->where(fn ($x) => $x
                ->where('employee_name', 'like', "%{$s}%")
                ->orWhere('employee_no', 'like', "%{$s}%"));
        }

        if ($request->filled('date_from')) {
            $base->where('check_time', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $base->where('check_time', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $rows = $isSearch
            ? $base->selectRaw('
                    employee_no,
                    employee_name,
                    DATE(check_time) as log_date,
                    MIN(check_time) as time_in,
                    MAX(check_time) as time_out
                ')
                ->whereNotNull('employee_no')
                ->groupBy('employee_no', 'employee_name', 'log_date')
                ->orderByDesc('log_date')
                ->paginate(20)
                ->withQueryString()
            : MirasolBiometricsLog::query()->whereRaw('1=0')->paginate(20);

        return view('hr_department.mirasol_logs.index', [
            'rows' => $rows,
            'all' => collect(),
            'people' => $people,
            'isSearch' => $isSearch,
        ]);
    }

    public function startSync(Request $request)
    {
        try {
            $v = $request->validate([
                'from' => ['required', 'date'],
                'to'   => ['required', 'date', 'after_or_equal:from'],
            ]);

            $from = Carbon::parse($v['from'])->startOfDay();
            $to   = Carbon::parse($v['to'])->endOfDay();

            $jobId = (string) Str::uuid();
            $key   = "crosschex_sync_status:{$jobId}";

            Cache::put($key, [
                'state' => 'queued',
                'message' => 'Queued...',
                'from' => $from->toDateTimeString(),
                'to' => $to->toDateTimeString(),
                'page' => 0,
                'pageCount' => null,
                'saved' => 0,
                'percent' => 0,
                'done' => false,
                'error' => null,
            ], now()->addMinutes(60));

            CrossChexSyncLogsJob::dispatch($jobId, $from->toDateTimeString(), $to->toDateTimeString());

            return response()->json([
                'ok' => true,
                'jobId' => $jobId,
                'date_from' => $from->toDateString(),
                'date_to' => $to->toDateString(),
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
            $v = $request->validate(['job' => ['required', 'string']]);

            $key = "crosschex_sync_status:{$v['job']}";
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
