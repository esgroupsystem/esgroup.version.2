<?php

namespace App\Http\Controllers\IT_Department;

use App\Http\Controllers\Controller;
use App\Models\BusDetail;
use App\Models\CctvConcern;
use App\Models\User;
use Illuminate\Http\Request;

class CctvController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status');

        // Base query (shared by table + monitoring)
        $baseQuery = CctvConcern::query()
            ->with(['assignee:id,full_name'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($x) use ($q) {
                    $x->where('jo_no', 'like', "%{$q}%")
                        ->orWhere('bus_no', 'like', "%{$q}%")
                        ->orWhere('reported_by', 'like', "%{$q}%")
                        ->orWhere('problem_details', 'like', "%{$q}%");
                });
            })
            ->when(! empty($status), fn ($query) => $query->where('status', $status));

        // Table (paginated)
        $jobOrders = (clone $baseQuery)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Monitoring (ALL records under current filters)
        $allJobOrders = (clone $baseQuery)->get();

        // Assignees (agents)
        $agents = User::orderBy('full_name')->get(['id', 'full_name']);

        // Buses
        $buses = BusDetail::orderBy('body_number')
            ->get(['id', 'garage', 'name', 'body_number', 'plate_number']);

        return view('it_department.cctv_concern', compact(
            'jobOrders',
            'allJobOrders',
            'agents',
            'buses'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bus_no' => 'required|string|max:50',
            'issue_type' => 'required|string|max:80',
            'cctv_part' => 'nullable|string|max:255',
            'problem_details' => 'required|string',
            'status' => 'required|string|max:30',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $data['reported_by'] = auth()->user()->full_name;
        $data['created_by'] = auth()->id();

        // Generate JO
        $year = now()->year;
        $last = CctvConcern::where('jo_no', 'like', "JO-$year-%")->latest()->first();
        $next = $last ? intval(substr($last->jo_no, -5)) + 1 : 1;

        $data['jo_no'] = "JO-$year-".str_pad($next, 5, '0', STR_PAD_LEFT);

        CctvConcern::create($data);

        return redirect()
            ->route('concern.cctv.index')
            ->with('success', 'CCTV Job Order created successfully.');
    }

    public function update(Request $request, $id)
    {
        $jobOrder = CctvConcern::findOrFail($id);

        $data = $request->validate([
            'bus_no' => ['required', 'string', 'max:50'],
            'reported_by' => ['nullable', 'string', 'max:255'],
            'issue_type' => ['required', 'string', 'max:80'],
            'cctv_part' => ['nullable', 'string', 'max:255'],
            'problem_details' => ['required', 'string'],
            'action_taken' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:30'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        if (in_array($data['status'], ['Fixed', 'Closed']) && ! $jobOrder->fixed_at) {
            $data['fixed_at'] = now();
        }
        if (! in_array($data['status'], ['Fixed', 'Closed'])) {
            $data['fixed_at'] = null;
        }

        $jobOrder->update($data);

        return redirect()->route('concern.cctv.index')->with('success', 'Job Order updated.');
    }

    public function destroy($id)
    {
        CctvConcern::findOrFail($id)->delete();

        return redirect()->route('concern.cctv.index')->with('success', 'Job Order deleted.');
    }

    public function view($id)
    {
        $jobOrder = CctvConcern::with(['assignee', 'creator'])->findOrFail($id);

        return view('it_department.cctv_concern', compact('jobOrder'));
    }

    // keep your endpoints
    public function acceptTask($id)
    {
        return back();
    }

    public function markAsDone($id)
    {
        return back();
    }

    public function addNote(Request $request, $id)
    {
        return back();
    }

    public function addFiles(Request $request, $id)
    {
        return back();
    }

    public function export($type)
    { /* ... */
    }
}
