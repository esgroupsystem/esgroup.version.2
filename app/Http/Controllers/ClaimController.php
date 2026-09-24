<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ClaimRequest;
use App\Models\Claim;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClaimController extends Controller
{
    public const TYPES = ['SSS', 'MATERNITY', 'PATERNITY', 'SICKNESS', 'RETIREMENT'];

    public const STATUSES = ['Draft', 'Ongoing', 'Approved', 'Requested', 'Released', 'Rejected'];

    public function index(Request $request): Response
    {
        $base = Claim::query()->with('employee');

        // SEARCH (employee name OR reference #)
        if ($request->filled('q')) {
            $search = trim($request->q);
            $base->where(function ($x) use ($search) {
                $x->where('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($e) use ($search) {
                        $e->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filters
        if ($request->filled('employee_id')) {
            $base->where('employee_id', $request->employee_id);
        }
        if ($request->filled('claim_type')) {
            $base->where('claim_type', $request->claim_type);
        }
        if ($request->filled('status')) {
            $base->where('status', $request->status);
        }

        // Date-range filter
        $dateField = $request->input('date_field', 'date_filed');
        $allowedDateFields = [
            'date_of_notification',
            'date_filed',
            'approval_date',
            'fund_request_date',
            'fund_released_date',
        ];
        if (! in_array($dateField, $allowedDateFields, true)) {
            $dateField = 'date_filed';
        }

        if ($request->filled('date_from')) {
            $base->whereDate($dateField, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $base->whereDate($dateField, '<=', $request->date_to);
        }

        // Monitoring (unpaginated under same filters)
        $allClaims = (clone $base)->get();

        // Table (paginated)
        $claims = $base->latest()->paginate(15)->withQueryString();

        $employees = Employee::query()
            ->select('id', 'full_name')
            ->orderBy('full_name')
            ->get();

        $date = static fn ($value): ?string => $value ? Carbon::parse($value)->format('Y-m-d') : null;
        $user = $request->user();

        return Inertia::render('hr/claims/index', [
            'claims' => $claims->through(fn (Claim $claim): array => [
                'id' => $claim->id,
                'employee_id' => (string) $claim->employee_id,
                'employee' => $claim->employee?->full_name,
                'claim_type' => $claim->claim_type,
                'status' => $claim->status,
                'reference_no' => $claim->reference_no,
                'date_of_notification' => $date($claim->date_of_notification),
                'date_filed' => $date($claim->date_filed),
                'approval_date' => $date($claim->approval_date),
                'fund_request_date' => $date($claim->fund_request_date),
                'fund_released_date' => $date($claim->fund_released_date),
                'amount' => $claim->amount !== null ? (string) $claim->amount : null,
                'remarks' => $claim->remarks,
            ]),
            'statusCounts' => $allClaims->groupBy('status')->map->count(),
            'employees' => $employees->map(fn (Employee $employee): array => ['value' => (string) $employee->id, 'label' => (string) $employee->full_name])->values(),
            'types' => self::TYPES,
            'statuses' => self::STATUSES,
            'dateFields' => [
                'date_of_notification' => 'Notification',
                'date_filed' => 'Filed',
                'approval_date' => 'Approval',
                'fund_request_date' => 'Fund request',
                'fund_released_date' => 'Fund released',
            ],
            'filters' => [
                'q' => (string) $request->input('q', ''),
                'employee_id' => (string) $request->input('employee_id', ''),
                'claim_type' => (string) $request->input('claim_type', ''),
                'status' => (string) $request->input('status', ''),
                'date_field' => $dateField,
                'date_from' => (string) $request->input('date_from', ''),
                'date_to' => (string) $request->input('date_to', ''),
            ],
            'can' => [
                'create' => (bool) $user?->can('claims.create'),
                'update' => (bool) $user?->can('claims.update'),
                'delete' => (bool) $user?->can('claims.delete'),
            ],
            'urls' => [
                'index' => route('claims.index'),
                'store' => route('claims.store'),
                'update' => route('claims.update', '__ID__'),
                'destroy' => route('claims.destroy', '__ID__'),
            ],
        ]);
    }

    public function store(ClaimRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        Claim::create($data);

        return redirect()
            ->route('claims.index')
            ->with('success', 'Claim created successfully.');
    }

    public function update(ClaimRequest $request, Claim $claim)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $claim->update($data);

        return redirect()
            ->route('claims.index')
            ->with('success', 'Claim updated successfully.');
    }

    public function destroy(Claim $claim)
    {
        $claim->delete();

        return redirect()
            ->route('claims.index')
            ->with('success', 'Claim deleted successfully.');
    }
}
