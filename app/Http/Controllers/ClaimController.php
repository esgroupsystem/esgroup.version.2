<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClaimRequest;
use App\Models\Claim;
use App\Models\Employee;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function index(Request $request)
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
        $dateField = $request->get('date_field', 'date_filed');
        $allowedDateFields = [
            'date_of_notification',
            'date_filed',
            'approval_date',
            'fund_request_date',
            'fund_released_date',
        ];
        if (!in_array($dateField, $allowedDateFields, true)) {
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

        return view('hr_department.claims.index', compact('claims', 'allClaims', 'employees', 'dateField'));
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
