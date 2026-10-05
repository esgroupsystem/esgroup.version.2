<?php

declare(strict_types=1);

namespace App\Http\Controllers\Biometrics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Biometrics\StoreManualBiometricsRequest;
use App\Models\MirasolBiometricsLog;
use App\Services\Biometrics\ManualBiometricsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Biometrics → Manual Biometrics (manual-biometrics.*): WFH Time In / Out per cutoff day. */
final class ManualBiometricsController extends Controller
{
    public function __construct(private readonly ManualBiometricsService $manualService) {}

    public function index(Request $request): Response
    {
        [$defaultMonth, $defaultYear, $defaultType] = $this->manualService->defaultCutoff();
        $month = (int) ($request->input('cutoff_month') ?: $defaultMonth);
        $year = (int) ($request->input('cutoff_year') ?: $defaultYear);
        $type = (string) ($request->input('cutoff_type') ?: $defaultType);
        [$start, $end, $label] = $this->manualService->cutoffRange($year, $month, $type);
        $employee = $this->manualService->employee((int) $request->input('employee_biometric_id'));

        return Inertia::render('payroll/manual-biometrics/index', [
            'filters' => ['cutoff_month' => $month, 'cutoff_year' => $year, 'cutoff_type' => $type],
            'cutoffLabel' => $label,
            'selectedEmployee' => $employee ? [
                ...$employee,
                'label' => $employee['employee_display_name'].' | '.($employee['employee_no'] ?? '-'),
            ] : null,
            'cutoffRows' => $employee ? $this->manualService->grid($employee, $start, $end) : [],
            'recentLogs' => $employee
                ? $this->manualService->manualLogs($employee, $start, $end)->map(fn (MirasolBiometricsLog $log): array => [
                    'id' => $log->id,
                    'check_time' => $log->check_time ? Carbon::parse($log->check_time)->format('M d, Y h:i A') : null,
                    'state' => $log->state,
                    'device_name' => $log->device_name,
                    'remarks' => data_get($log->raw, 'remarks') ?: '-',
                ])->values()
                : [],
            'can' => ['create' => (bool) $request->user()?->can('manual-biometrics.create')],
            'urls' => [
                'index' => route('manual-biometrics.index'),
                'search' => route('manual-biometrics.search-employees'),
                'store' => route('manual-biometrics.store'),
            ],
        ]);
    }

    public function searchEmployees(Request $request): JsonResponse
    {
        return response()->json($this->manualService->search((string) $request->input('q')));
    }

    public function store(StoreManualBiometricsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $message = $this->manualService->save($data, $request->user()?->id);

        return redirect()->route('manual-biometrics.index', [
            'cutoff_month' => $data['cutoff_month'],
            'cutoff_year' => $data['cutoff_year'],
            'cutoff_type' => $data['cutoff_type'],
            'employee_biometric_id' => $data['employee_biometric_id'],
        ])->with('success', $message);
    }
}
