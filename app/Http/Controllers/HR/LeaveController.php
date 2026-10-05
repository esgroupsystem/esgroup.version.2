<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Enums\LeaveKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\LeaveActionRequest;
use App\Http\Requests\HR\LeaveRecordRequest;
use App\Http\Resources\HR\LeaveEmployeeResource;
use App\Http\Resources\HR\LeaveFormResource;
use App\Http\Resources\HR\LeaveRowResource;
use App\Models\Employee;
use App\Models\LeaveRecord;
use App\Services\HR\LeaveNoticeService;
use App\Services\HR\LeaveService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * Human Resources → Leaves. The Admin, Driver and Conductor pages share this controller;
 * each subclass only names its LeaveKind (routes, permissions and wording follow from it).
 */
abstract class LeaveController extends Controller
{
    public function __construct(
        private readonly LeaveService $leaves,
        private readonly LeaveNoticeService $notices,
    ) {}

    abstract protected function kind(): LeaveKind;

    public function index(Request $request): Response
    {
        $kind = $this->kind();
        $user = $request->user();
        $canUpdate = (bool) $user?->can($kind->permission().'.update');
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'status' => strtolower(trim((string) $request->input('status', ''))),
            'leave_type' => trim((string) $request->input('leave_type', '')),
            'garage' => trim((string) $request->input('garage', '')),
        ];
        $today = Carbon::now('Asia/Manila')->startOfDay();
        $summary = $this->leaves->summary($kind, $filters['search']);

        return Inertia::render('hr/leaves/index', [
            'kind' => $this->kindProps(),
            'leaves' => $this->leaves->paginate($kind, $filters['search'], $filters['status'], $filters['leave_type'], $filters['garage'])
                ->through(fn (LeaveRecord $leave): array => (new LeaveRowResource($leave, $kind, $today, $canUpdate))->resolve($request)),
            'counts' => $summary['counts'],
            'garageSummary' => $summary['garageSummary'],
            'filters' => $filters,
            'leaveTypes' => LeaveRecord::TYPES,
            'garages' => Employee::GARAGES,
            'can' => [
                'create' => (bool) $user?->can($kind->permission().'.create'),
                'update' => $canUpdate,
            ],
            'urls' => [
                'index' => route($kind->routeName().'.index'),
                'create' => route($kind->routeName().'.create'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->form($request, null);
    }

    public function store(LeaveRecordRequest $request): RedirectResponse
    {
        $kind = $this->kind();

        try {
            $this->leaves->create($kind, $request->validated());
            flash("{$kind->noun()} leave created successfully.")->success();
        } catch (DomainException $exception) {
            flash($exception->getMessage())->warning();

            return back()->withInput();
        }

        return redirect()->route($kind->routeName().'.index');
    }

    public function edit(Request $request, int $leave): Response
    {
        return $this->form($request, $this->leaves->find($this->kind(), $leave, ['employee.position']));
    }

    public function update(LeaveRecordRequest $request, int $leave): RedirectResponse
    {
        $kind = $this->kind();
        $record = $this->leaves->find($kind, $leave);

        try {
            $this->leaves->update($kind, $record, $request->validated());
            flash("{$kind->noun()} leave updated successfully.")->success();
        } catch (DomainException $exception) {
            flash($exception->getMessage())->warning();

            return back()->withInput();
        }

        return redirect()->route($kind->routeName().'.index');
    }

    public function action(LeaveActionRequest $request, int $leave): RedirectResponse
    {
        $kind = $this->kind();
        $record = $this->leaves->find($kind, $leave);

        try {
            flash($this->notices->handle(
                $kind,
                $record,
                (string) $request->validated('action_type'),
                $request->validated('note'),
                $request->file('proof_image'),
            ))->success();
        } catch (DomainException $exception) {
            flash($exception->getMessage())->warning();
        } catch (Throwable $exception) {
            Log::error("{$kind->noun()} leave action failed.", [
                "{$kind->value}_leave_id" => $record->id,
                'action_type' => $request->input('action_type'),
                'user_id' => $request->user()?->getKey(),
                'exception' => $exception,
            ]);
            flash($kind->actionFailedMessage())->error();
        }

        return redirect()->route($kind->routeName().'.index');
    }

    /** A notice's picture proof, shown inline. */
    public function proof(int $leave, string $type): BinaryFileResponse
    {
        return response()->file($this->leaves->proofPath($this->kind(), $leave, $type), [
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function form(Request $request, ?LeaveRecord $leave): Response
    {
        $kind = $this->kind();
        $route = $kind->routeName();
        $form = $leave ? LeaveFormResource::make($leave)->resolve($request) : ['leave' => null, 'values' => LeaveFormResource::blankValues()];

        return Inertia::render('hr/leaves/form', [
            'kind' => $this->kindProps(),
            'leave' => $form['leave'],
            'values' => $form['values'],
            'employees' => $this->employeeOptions($request, $this->leaves->candidates($kind, $leave)),
            'leaveTypes' => LeaveRecord::TYPES,
            'urls' => [
                'index' => route("{$route}.index"),
                'submit' => $leave ? route("{$route}.update", $leave->id) : route("{$route}.store"),
            ],
        ]);
    }

    /**
     * @param  Collection<int, Employee>  $employees
     * @return list<array<string, string>>
     */
    private function employeeOptions(Request $request, Collection $employees): array
    {
        return $employees->map(fn (Employee $employee): array => LeaveEmployeeResource::make($employee)->asOption($request))->values()->all();
    }

    /** @return array{key: string, noun: string, title: string} */
    private function kindProps(): array
    {
        $kind = $this->kind();

        return ['key' => $kind->value, 'noun' => $kind->noun(), 'title' => $kind->title()];
    }
}
