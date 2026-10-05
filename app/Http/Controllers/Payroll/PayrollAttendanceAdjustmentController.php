<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\OffsetProofRequest;
use App\Http\Requests\Payroll\OvertimeCheckRequest;
use App\Http\Requests\Payroll\PayrollAttendanceAdjustmentRequest;
use App\Http\Requests\Payroll\RejectAttendanceAdjustmentRequest;
use App\Http\Resources\Payroll\AttendanceAdjustmentFormResource;
use App\Http\Resources\Payroll\AttendanceAdjustmentRowResource;
use App\Models\EmployeeBiometric;
use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment as Adjustment;
use App\Services\Payroll\AttendanceAdjustmentService;
use App\Services\Payroll\OffsetCreditService;
use App\Services\Payroll\OvertimeCheckService;
use App\Support\PayrollEmployeeNameFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Payroll → Adjustment. Approve / reject is for OT, offset and salary adjustments.
 */
final class PayrollAttendanceAdjustmentController extends Controller
{
    public function __construct(
        private readonly AttendanceAdjustmentService $adjustments,
        private readonly OffsetCreditService $offsets,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Adjustment::class);

        $filters = $this->adjustments->filters($request->query());
        $status = trim((string) $request->input('status', ''));
        $user = $request->user();

        return Inertia::render('payroll/adjustments/index', [
            'adjustments' => $this->adjustments->paginate($filters, $status)
                ->through(fn (Adjustment $adjustment): array => AttendanceAdjustmentRowResource::make($adjustment)->resolve($request)),
            'stats' => $this->adjustments->stats($filters, $status),
            'filters' => [...$filters, 'status' => $status],
            'groups' => collect(EmployeeBiometric::GROUP_LABELS)->mapWithKeys(fn (string $label, int $group): array => [(string) $group => $label])->all(),
            'types' => Adjustment::TYPES,
            'can' => [
                'create' => $user->can('create', Adjustment::class),
                'update' => $user->can('payroll-attendance-adjustments.update'),
                'delete' => $user->can('payroll-attendance-adjustments.delete'),
                'approve' => $user->can('payroll.finalize'),
            ],
            'urls' => [
                'index' => route('payroll-attendance-adjustments.index'),
                'create' => route('payroll-attendance-adjustments.create'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Adjustment::class);

        return $this->form($request, null);
    }

    public function store(PayrollAttendanceAdjustmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Adjustment::class);

        $result = $this->adjustments->create(
            $request->validated(),
            $request->has('is_paid') ? $request->boolean('is_paid') : null,
            $request->file('ot_form'),
            (int) $request->input('recompute_payroll_item_id', 0),
            (bool) $request->user()?->can('create', Payroll::class),
            $this->userId($request),
        );

        // From the "File adjustment" dialog: back to that payroll item.
        if ($result['item'] !== null) {
            return redirect()->route('payroll.items.show', [$result['item']->payroll_id, $result['item']->id])->with('success', $result['message']);
        }

        return redirect()->route('payroll-attendance-adjustments.index')->with('success', $result['message']);
    }

    public function edit(Request $request, Adjustment $payrollAttendanceAdjustment): Response
    {
        $this->authorize('update', $payrollAttendanceAdjustment);

        return $this->form($request, $payrollAttendanceAdjustment);
    }

    public function update(PayrollAttendanceAdjustmentRequest $request, Adjustment $payrollAttendanceAdjustment): RedirectResponse
    {
        $this->authorize('update', $payrollAttendanceAdjustment);

        $message = $this->adjustments->update(
            $payrollAttendanceAdjustment,
            $request->validated(),
            $request->has('is_paid') ? $request->boolean('is_paid') : null,
            $request->file('ot_form'),
            $this->userId($request),
        );

        return redirect()->route('payroll-attendance-adjustments.index')->with('success', $message);
    }

    public function approve(Request $request, Adjustment $payrollAttendanceAdjustment): RedirectResponse
    {
        $this->authorize('approve', $payrollAttendanceAdjustment);

        return back()->with('success', $this->adjustments->approve($payrollAttendanceAdjustment, $this->userId($request)));
    }

    public function reject(RejectAttendanceAdjustmentRequest $request, Adjustment $payrollAttendanceAdjustment): RedirectResponse
    {
        $this->authorize('reject', $payrollAttendanceAdjustment);

        return back()->with('success', $this->adjustments->reject($payrollAttendanceAdjustment, $request->validated('rejection_reason'), $this->userId($request)));
    }

    public function destroy(Adjustment $payrollAttendanceAdjustment): RedirectResponse
    {
        $this->authorize('delete', $payrollAttendanceAdjustment);

        $this->adjustments->delete($payrollAttendanceAdjustment);

        return redirect()->route('payroll-attendance-adjustments.index')->with('success', 'Payroll attendance adjustment deleted successfully.');
    }

    /** Live OT checker for the form (same rules as the save). */
    public function overtimeCheck(OvertimeCheckRequest $request, OvertimeCheckService $check): JsonResponse
    {
        $this->authorize('offsetProof', Adjustment::class);
        $data = $request->validated();

        return response()->json($check->check(
            (int) $data['employee_biometric_id'],
            $data['biometric_employee_id'] ?? null,
            $data['employee_no'] ?? null,
            $data['employee_name'],
            $data['work_date'],
            $data['adjusted_time_in'],
            $data['adjusted_time_out'],
            isset($data['adjustment_id']) ? (int) $data['adjustment_id'] : null,
        ));
    }

    /** "Check available offset credit" in the form. */
    public function offsetProof(OffsetProofRequest $request): JsonResponse
    {
        $this->authorize('offsetProof', Adjustment::class);

        [$payload, $status] = $this->offsets->report($request->validated());

        return response()->json($payload, $status);
    }

    /** The uploaded approved OT form (private file, shown inline). */
    public function attachment(Adjustment $payrollAttendanceAdjustment): BinaryFileResponse
    {
        $this->authorize('viewAny', Adjustment::class);

        ['path' => $path, 'name' => $name, 'mime' => $mime] = $this->adjustments->attachment($payrollAttendanceAdjustment);

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.addcslashes($name, '"\\').'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function form(Request $request, ?Adjustment $adjustment): Response
    {
        return Inertia::render('payroll/adjustments/form', [
            'adjustment' => $adjustment ? AttendanceAdjustmentFormResource::make($adjustment)->resolve($request) : null,
            'people' => $this->adjustments->people()
                ->map(fn (array $person): array => [...$person, 'display_name' => PayrollEmployeeNameFormatter::display($person['employee_name'])])
                ->values(),
            'types' => Adjustment::TYPES,
            'typeRules' => collect(Adjustment::TYPES)->keys()->mapWithKeys(fn (string $type): array => [$type => Adjustment::rulesFor($type)]),
            'urls' => [
                'index' => route('payroll-attendance-adjustments.index'),
                'submit' => $adjustment ? route('payroll-attendance-adjustments.update', $adjustment) : route('payroll-attendance-adjustments.store'),
                'offsetProof' => route('payroll-attendance-adjustments.offset-proof'),
                'overtimeCheck' => route('payroll-attendance-adjustments.overtime-check'),
            ],
        ]);
    }

    private function userId(Request $request): ?int
    {
        $id = $request->user()?->getKey();

        return $id === null ? null : (int) $id;
    }
}
