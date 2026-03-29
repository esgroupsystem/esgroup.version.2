<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\MirasolBiometricsLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualBiometricsEncodingController extends Controller
{
    public function index(Request $request)
    {
        [$defaultCutoffMonth, $defaultCutoffYear, $defaultCutoffType] = $this->getDefaultCutoff();

        $cutoffMonth = (int) ($request->cutoff_month ?: $defaultCutoffMonth);
        $cutoffYear = (int) ($request->cutoff_year ?: $defaultCutoffYear);
        $cutoffType = (string) ($request->cutoff_type ?: $defaultCutoffType);

        [$startDate, $endDate, $cutoffLabel] = $this->resolveCutoffRange($cutoffYear, $cutoffMonth, $cutoffType);

        $selectedCrosschexId = trim((string) $request->crosschex_id);

        $selectedEmployee = null;
        $cutoffRows = collect();
        $recentLogs = collect();

        if ($selectedCrosschexId !== '') {
            $selectedEmployee = $this->findEmployeeByCrosschexId($selectedCrosschexId);

            if ($selectedEmployee) {
                $existingLogs = MirasolBiometricsLog::query()
                    ->where('crosschex_id', $selectedCrosschexId)
                    ->whereBetween('check_time', [
                        $startDate->copy()->startOfDay(),
                        $endDate->copy()->endOfDay(),
                    ])
                    ->orderBy('check_time')
                    ->get()
                    ->groupBy(function ($log) {
                        return Carbon::parse($log->check_time)->format('Y-m-d');
                    });

                $period = CarbonPeriod::create($startDate, $endDate);

                foreach ($period as $date) {
                    $dayLogs = collect($existingLogs->get($date->format('Y-m-d'), []));

                    $checkInLog = $dayLogs->first(function ($log) {
                        return strtolower((string) $log->state) === 'check in';
                    });

                    $checkOutLog = $dayLogs->first(function ($log) {
                        return strtolower((string) $log->state) === 'check out';
                    });

                    $cutoffRows->push([
                        'work_date' => $date->format('Y-m-d'),
                        'day_name' => $date->format('D'),
                        'time_in' => $checkInLog ? Carbon::parse($checkInLog->check_time)->format('H:i') : null,
                        'time_out' => $checkOutLog ? Carbon::parse($checkOutLog->check_time)->format('H:i') : null,
                        'remarks' => data_get($checkInLog?->raw, 'remarks')
                            ?: data_get($checkOutLog?->raw, 'remarks'),
                        'has_manual_log' => $dayLogs->contains(function ($log) {
                            return (string) $log->device_sn === 'WFH-MANUAL';
                        }),
                    ]);
                }

                $recentLogs = MirasolBiometricsLog::query()
                    ->where('crosschex_id', $selectedCrosschexId)
                    ->where('device_sn', 'WFH-MANUAL')
                    ->whereBetween('check_time', [
                        $startDate->copy()->startOfDay(),
                        $endDate->copy()->endOfDay(),
                    ])
                    ->orderBy('check_time')
                    ->get();
            }
        }

        return view('payroll.manual_biometrics.index', compact(
            'cutoffMonth',
            'cutoffYear',
            'cutoffType',
            'cutoffLabel',
            'startDate',
            'endDate',
            'selectedCrosschexId',
            'selectedEmployee',
            'cutoffRows',
            'recentLogs'
        ));
    }

    public function searchEmployees(Request $request)
    {
        $search = trim((string) $request->get('q'));

        $employees = MirasolBiometricsLog::query()
            ->select(
                'crosschex_id',
                DB::raw('MAX(employee_id) as employee_id'),
                DB::raw('MAX(employee_no) as employee_no'),
                DB::raw('MAX(employee_name) as employee_name')
            )
            ->whereNotNull('crosschex_id')
            ->whereNotNull('employee_name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%")
                        ->orWhere('crosschex_id', 'like', "%{$search}%");
                });
            })
            ->groupBy('crosschex_id')
            ->orderBy(DB::raw('MAX(employee_name)'))
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'crosschex_id' => $item->crosschex_id,
                    'employee_id' => $item->employee_id,
                    'employee_no' => $item->employee_no,
                    'employee_name' => $item->employee_name,
                    'label' => trim(($item->employee_name ?? 'Unknown').' | '.($item->employee_no ?? '-').' | '.($item->crosschex_id ?? '-')),
                ];
            })
            ->values();

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cutoff_month' => ['required', 'integer', 'min:1', 'max:12'],
            'cutoff_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'cutoff_type' => ['required', 'in:first,second'],
            'crosschex_id' => ['required', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:255'],
            'employee_no' => ['nullable', 'string', 'max:255'],
            'employee_name' => ['required', 'string', 'max:255'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.work_date' => ['required', 'date'],
            'rows.*.time_in' => ['nullable', 'date_format:H:i'],
            'rows.*.time_out' => ['nullable', 'date_format:H:i'],
            'rows.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        [$startDate, $endDate] = $this->resolveCutoffRange(
            (int) $validated['cutoff_year'],
            (int) $validated['cutoff_month'],
            (string) $validated['cutoff_type']
        );

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($validated['rows'] as $row) {
                $workDate = Carbon::parse($row['work_date'], 'Asia/Manila')->format('Y-m-d');

                $workDateCarbon = Carbon::parse($workDate, 'Asia/Manila');

                if (
                    $workDateCarbon->lt($startDate->copy()->startOfDay()) ||
                    $workDateCarbon->gt($endDate->copy()->endOfDay())
                ) {
                    continue;
                }

                $timeIn = $row['time_in'] ?? null;
                $timeOut = $row['time_out'] ?? null;
                $remarks = $row['remarks'] ?? null;

                if (blank($timeIn) && blank($timeOut)) {
                    $skipped++;

                    continue;
                }

                $commonData = [
                    'crosschex_id' => $validated['crosschex_id'],
                    'employee_id' => $validated['employee_id'] ?? null,
                    'employee_no' => $validated['employee_no'] ?? null,
                    'employee_name' => $validated['employee_name'],
                    'device_sn' => 'WFH-MANUAL',
                    'device_name' => 'WFH Manual Encoding',
                ];

                if ($timeIn) {
                    $checkInDateTime = Carbon::createFromFormat(
                        'Y-m-d H:i',
                        $workDate.' '.$timeIn,
                        'Asia/Manila'
                    );

                    $existing = MirasolBiometricsLog::query()
                        ->where('employee_no', $validated['employee_no'])
                        ->where('check_time', $checkInDateTime->format('Y-m-d H:i:s'))
                        ->where('device_sn', 'WFH-MANUAL')
                        ->first();

                    if ($existing) {
                        $existing->fill(array_merge($commonData, [
                            'state' => 'Check In',
                            'raw' => [
                                'source' => 'manual_wfh_cutoff_encoding',
                                'type' => 'time_in',
                                'remarks' => $remarks,
                                'encoded_at' => now('Asia/Manila')->toDateTimeString(),
                                'cutoff_month' => (int) $validated['cutoff_month'],
                                'cutoff_year' => (int) $validated['cutoff_year'],
                                'cutoff_type' => (string) $validated['cutoff_type'],
                            ],
                        ]));
                        $existing->save();
                        $updated++;
                    } else {
                        MirasolBiometricsLog::create(array_merge($commonData, [
                            'check_time' => $checkInDateTime->format('Y-m-d H:i:s'),
                            'state' => 'Check In',
                            'raw' => [
                                'source' => 'manual_wfh_cutoff_encoding',
                                'type' => 'time_in',
                                'remarks' => $remarks,
                                'encoded_at' => now('Asia/Manila')->toDateTimeString(),
                                'cutoff_month' => (int) $validated['cutoff_month'],
                                'cutoff_year' => (int) $validated['cutoff_year'],
                                'cutoff_type' => (string) $validated['cutoff_type'],
                            ],
                        ]));
                        $created++;
                    }
                }

                if ($timeOut) {
                    $checkOutDateTime = Carbon::createFromFormat(
                        'Y-m-d H:i',
                        $workDate.' '.$timeOut,
                        'Asia/Manila'
                    );

                    $existing = MirasolBiometricsLog::query()
                        ->where('employee_no', $validated['employee_no'])
                        ->where('check_time', $checkOutDateTime->format('Y-m-d H:i:s'))
                        ->where('device_sn', 'WFH-MANUAL')
                        ->first();

                    if ($existing) {
                        $existing->fill(array_merge($commonData, [
                            'state' => 'Check Out',
                            'raw' => [
                                'source' => 'manual_wfh_cutoff_encoding',
                                'type' => 'time_out',
                                'remarks' => $remarks,
                                'encoded_at' => now('Asia/Manila')->toDateTimeString(),
                                'cutoff_month' => (int) $validated['cutoff_month'],
                                'cutoff_year' => (int) $validated['cutoff_year'],
                                'cutoff_type' => (string) $validated['cutoff_type'],
                            ],
                        ]));
                        $existing->save();
                        $updated++;
                    } else {
                        MirasolBiometricsLog::create(array_merge($commonData, [
                            'check_time' => $checkOutDateTime->format('Y-m-d H:i:s'),
                            'state' => 'Check Out',
                            'raw' => [
                                'source' => 'manual_wfh_cutoff_encoding',
                                'type' => 'time_out',
                                'remarks' => $remarks,
                                'encoded_at' => now('Asia/Manila')->toDateTimeString(),
                                'cutoff_month' => (int) $validated['cutoff_month'],
                                'cutoff_year' => (int) $validated['cutoff_year'],
                                'cutoff_type' => (string) $validated['cutoff_type'],
                            ],
                        ]));
                        $created++;
                    }
                }
            }

            DB::commit();

            return redirect()->route('manual-biometrics.index', [
                'cutoff_month' => $validated['cutoff_month'],
                'cutoff_year' => $validated['cutoff_year'],
                'cutoff_type' => $validated['cutoff_type'],
                'crosschex_id' => $validated['crosschex_id'],
            ])->with('success', "WFH cutoff logs saved successfully. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors([
                    'error' => 'Failed to save manual cutoff biometrics logs. '.$e->getMessage(),
                ]);
        }
    }

    private function findEmployeeByCrosschexId(string $crosschexId): ?array
    {
        $employee = MirasolBiometricsLog::query()
            ->select(
                'crosschex_id',
                DB::raw('MAX(employee_id) as employee_id'),
                DB::raw('MAX(employee_no) as employee_no'),
                DB::raw('MAX(employee_name) as employee_name')
            )
            ->where('crosschex_id', $crosschexId)
            ->groupBy('crosschex_id')
            ->first();

        if (! $employee) {
            return null;
        }

        return [
            'crosschex_id' => $employee->crosschex_id,
            'employee_id' => $employee->employee_id,
            'employee_no' => $employee->employee_no,
            'employee_name' => $employee->employee_name,
        ];
    }

    private function getDefaultCutoff(): array
    {
        $today = now('Asia/Manila');

        $day = (int) $today->day;
        $month = (int) $today->month;
        $year = (int) $today->year;

        if ($day >= 11 && $day <= 25) {
            return [$month, $year, 'first'];
        }

        if ($day >= 26) {
            return [$month, $year, 'second'];
        }

        $previousMonth = $today->copy()->subMonth();

        return [
            (int) $previousMonth->month,
            (int) $previousMonth->year,
            'second',
        ];
    }

    private function resolveCutoffRange(int $year, int $month, string $type): array
    {
        $baseMonth = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila');

        if ($type === 'first') {
            $startDate = $baseMonth->copy()->day(11)->startOfDay();
            $endDate = $baseMonth->copy()->day(25)->endOfDay();
            $label = $startDate->format('F d, Y').' - '.$endDate->format('F d, Y');
        } else {
            $startDate = $baseMonth->copy()->day(26)->startOfDay();
            $endDate = $baseMonth->copy()->addMonth()->day(10)->endOfDay();
            $label = $startDate->format('F d, Y').' - '.$endDate->format('F d, Y');
        }

        return [$startDate, $endDate, $label];
    }
}
