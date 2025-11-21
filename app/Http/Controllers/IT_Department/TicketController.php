<?php

namespace App\Http\Controllers\IT_Department;

use App\Events\JobOrderCreated;
use App\Exports\JobOrdersExport;
use App\Helpers\Notifier;
use App\Http\Controllers\Controller;
use App\Mail\JobOrderCreatedMail;
use App\Models\BusDetail;
use App\Models\JobOrder;
use App\Models\JobOrderFile;
use App\Models\JobOrderLog;
use App\Models\JobOrderNote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class TicketController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index Routes
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $tab = $request->tab ?? 'pending';

        $query = JobOrder::with('bus')->orderBy('id', 'desc');

        if ($request->ajax()) {

            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('job_creator', 'like', "%{$request->search}%")
                        ->orWhere('job_type', 'like', "%{$request->search}%")
                        ->orWhere('job_status', 'like', "%{$request->search}%");
                });
            }

            if ($tab == 'pending') {
                $query->where('job_status', 'Pending');
            } elseif ($tab == 'progress') {
                $query->where('job_status', 'In Progress');
            } elseif ($tab == 'completed') {
                $query->where('job_status', 'Completed');
            }

            $list = $query->paginate(10);

            return view('tickets.partials.table', compact('list'))->render();
        }

        $pending = JobOrder::with('bus')->where('job_status', 'Pending')->paginate(10);
        $progress = JobOrder::with('bus')->where('job_status', 'In Progress')->paginate(10);
        $completed = JobOrder::with('bus')->where('job_status', 'Completed')->paginate(10);

        $stats = [
            'new' => JobOrder::whereDate('created_at', today())->count(),
            'pending' => JobOrder::where('job_status', 'Pending')->count(),
            'progress' => JobOrder::where('job_status', 'In Progress')->count(),
            'completed' => JobOrder::where('job_status', 'Completed')->count(),
        ];

        $categoryList = [
            'ACCIDENT', 'COLLECTING FARE', 'CUTTING FARE', 'RE- ISSUEING TICKET',
            'TAMPERING TICKET', 'UNREGISTERED TICKET', 'DELAYING ISSUANCE OF TICKET',
            'ROLLING TICKETS', 'REMOVING HEADSTAB OF TICKET', 'USING STUB TICKET',
            'WRONG CLOSING / OPEN', 'OTHERS',
        ];

        $categoryCounts = JobOrder::select('job_type')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('job_type')
            ->pluck('total', 'job_type');

        $categories = [];
        foreach ($categoryList as $cat) {
            $categories[] = [
                'name' => $cat,
                'total' => $categoryCounts[$cat] ?? 0,
            ];
        }

        $agents = User::whereIn('role', ['IT Head', 'IT Officer', 'IT Technician'])
            ->withCount(['jobOrdersAssigned'])
            ->get();

        return view('it_department.ticket_job_order', compact(
            'pending', 'progress', 'completed', 'stats', 'categories', 'agents'
        ));
    }

    public function cctvindex()
    {
        return view('it_department.cctv_concern');
    }

    public function createjobordersIndex()
    {
        $buses = BusDetail::orderBy('body_number')->get();

        return view('it_department.create_joborder', compact('buses'));
    }

    /*
    |--------------------------------------------------------------------------
    | View of details files Routes
    |--------------------------------------------------------------------------
    */

    public function view($id)
    {
        $job = JobOrder::with('bus', 'files', 'logs.user', 'notes.user')->findOrFail($id);

        $existingViewLog = JobOrderLog::where('joborder_id', $job->id)
            ->where('user_id', Auth::id())
            ->where('action', 'viewed')
            ->first();

        if ($existingViewLog) {
            $existingViewLog->update([
                'meta' => [
                    'message' => 'User viewed the job order details',
                ],
            ]);
        } else {

            JobOrderLog::create([
                'joborder_id' => $job->id,
                'user_id' => Auth::id(),
                'action' => 'viewed',
                'meta' => [
                    'message' => 'User viewed the job order details',
                ],
            ]);
        }

        $logs = $job->logs()->orderBy('created_at', 'desc')->get();

        return view('it_department.view_joborder', compact('job', 'logs'));
    }

    /*
    |--------------------------------------------------------------------------
    | Saving/Create Routes
    |--------------------------------------------------------------------------
    */

    public function storeJoborders(Request $request)
    {
        try {
            $validated = $request->validate([
                'garage' => ['nullable'],
                'bus_name' => ['nullable'],
                'body_number' => ['required', 'string', 'max:255'],
                'plate_number' => ['nullable', 'string', 'max:255'],

                'job_name' => ['nullable', 'string', 'max:255'],
                'job_type' => ['required', 'string', 'max:255'],
                'job_datestart' => ['required', 'string', 'max:255'],
                'job_time_start' => ['required', 'string', 'max:255'],
                'job_time_end' => ['required', 'string', 'max:255'],
                'job_sitNumber' => ['nullable', 'string', 'max:20'],
                'job_remarks' => ['nullable', 'string'],
                'job_status' => ['required', Rule::in(['Pending', 'Assigned', 'In Progress', 'Completed'])],
                'job_assign_person' => ['nullable', 'string', 'max:255'],
                'direction' => ['nullable', 'string', 'max:255'],
                'driver_name' => ['nullable', 'string', 'max:255'],
                'conductor_name' => ['nullable', 'string', 'max:255'],

                'files.*' => ['nullable', 'file', 'max:5120'],
            ]);

            $user = Auth::user();

            return DB::transaction(function () use ($validated, $request, $user) {

                $bus = BusDetail::where('body_number', $validated['body_number'])->first();

                if (! $bus) {
                    Log::warning('Bus not found', ['body_number' => $validated['body_number']]);
                    flash('Selected bus does not exist.')->error();

                    return back()->withInput();
                }

                $validated['garage'] = $bus->garage;
                $validated['bus_name'] = $bus->name;
                $validated['plate_number'] = $bus->plate_number;

                $job = JobOrder::create([
                    'bus_detail_id' => $bus->id,
                    'created_by' => optional($user)->id,
                    'job_name' => $validated['job_name'] ?? 'Job Order',
                    'job_type' => $validated['job_type'],
                    'job_datestart' => $validated['job_datestart'],
                    'job_time_start' => $validated['job_time_start'],
                    'job_time_end' => $validated['job_time_end'],
                    'job_sitNumber' => $validated['job_sitNumber'] ?? null,
                    'job_remarks' => $validated['job_remarks'] ?? null,
                    'job_status' => $validated['job_status'],
                    'job_assign_person' => $validated['job_assign_person'] ?? null,
                    'job_date_filled' => now(),
                    'job_creator' => optional($user)->full_name,
                    'driver_name' => $validated['driver_name'] ?? null,
                    'conductor_name' => $validated['conductor_name'] ?? null,
                    'direction' => $validated['direction'] ?? null,
                ]);

                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $upload) {
                        $stored = $upload->store("joborders/{$job->id}", 'public');

                        JobOrderFile::create([
                            'job_id' => $job->id,
                            'file_name' => $upload->getClientOriginalName(),
                            'file_remarks' => null,
                            'file_notes' => null,
                            'file_path' => $stored,
                        ]);
                    }
                }

                JobOrderLog::create([
                    'joborder_id' => $job->id,
                    'action' => 'created',
                    'meta' => [
                        'job_type' => $job->job_type,
                        'status' => $job->job_status,
                        'bus' => $bus->body_number,
                    ],
                    'user_id' => optional($user)->id,
                ]);

                event(new JobOrderCreated($job->load('bus')));
                Notifier::notifyRoles(
                    ['IT Head', 'IT Officer'],
                    new JobOrderCreatedMail($job)
                );

                flash("Job Order #{$job->id} created successfully!")->success();

                return redirect()->route('tickets.joborder.index');
            });

        } catch (ValidationException $e) {

            Log::warning('Job Order Validation Failed', [
                'route' => request()->route()->getName(),
                'errors' => $e->validator->errors()->toArray(),
                'input' => request()->except(['files']),
            ]);

            flash('Validation failed. Please check the fields.')->error();

            return back()
                ->withErrors($e->validator)
                ->withInput();

        } catch (\Exception $e) {

            Log::error('Job Order Creation Error', [
                'route' => request()->route()->getName(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input' => request()->except(['files']),
            ]);

            flash('Something went wrong while creating the job order.')->error();

            return back()
                ->with('debug', app()->environment('local') ? $e->getMessage() : null)
                ->withInput();
        }
    }

    public function addNote(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string',
            'details' => 'nullable|string',
        ]);

        JobOrderNote::create([
            'joborder_id' => $id,
            'user_id' => Auth::id(),
            'reason' => $request->reason,
            'details' => $request->details,
        ]);

        JobOrderLog::create([
            'joborder_id' => $id,
            'user_id' => Auth::id(),
            'action' => 'added note',
            'meta' => ['reason' => $request->reason],
        ]);

        return back();
    }

    public function addFiles(Request $request, $id)
    {
        $job = JobOrder::findOrFail($id);

        $request->validate([
            'files.*' => ['required', 'file', 'max:1024000'],
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $upload) {

                $stored = $upload->store("joborders/{$job->id}", 'public');

                JobOrderFile::create([
                    'job_id' => $job->id,
                    'file_name' => $upload->getClientOriginalName(),
                    'file_path' => $stored,
                ]);
            }

            JobOrderLog::create([
                'joborder_id' => $job->id,
                'user_id' => Auth::id(),
                'action' => 'added file',
                'meta' => [
                    'file_count' => count($request->file('files')),
                ],
            ]);
        }
        flash('Files uploaded successfully.')->info();

        return back();
    }

    public function export($type)
    {
        $data = JobOrder::with('bus')->get();

        if ($type === 'excel') {
            return Excel::download(new JobOrdersExport, 'job_orders.xlsx');
        }

        if ($type === 'pdf') {
            $pdf = PDF::loadView('it_department.export.pdf', compact('data'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('job_orders.pdf');
        }

        return back()->with('error', 'Invalid export type selected.');
    }

    /*
    |--------------------------------------------------------------------------
    | Update files Routes
    |--------------------------------------------------------------------------
    */

    public function acceptTask($id)
    {
        $job = JobOrder::findOrFail($id);

        if ($job->job_status !== 'Pending') {
            return back();
        }

        $job->update([
            'job_assign_person' => Auth::user()->full_name,
            'job_status' => 'In Progress',
        ]);

        JobOrderLog::create([
            'joborder_id' => $job->id,
            'user_id' => Auth::id(),
            'action' => 'accepted task',
            'meta' => ['message' => 'Task accepted by IT officer'],
        ]);

        return back();
    }

    public function markAsDone($id)
    {
        $job = JobOrder::findOrFail($id);

        if ($job->job_status !== 'In Progress') {
            return back();
        }

        $job->update([
            'job_status' => 'Completed',
        ]);

        JobOrderLog::create([
            'joborder_id' => $job->id,
            'user_id' => Auth::id(),
            'action' => 'completed',
            'meta' => ['message' => 'Task marked as done'],
        ]);

        return back();
    }

    public function update(Request $request, $id)
    {
        $job = JobOrder::findOrFail($id);

        // Normalize date
        if ($request->job_datestart) {
            if (preg_match('/\d{4}-\d{2}-\d{2}/', $request->job_datestart)) {
                $request->merge([
                    'job_datestart' => Carbon::parse($request->job_datestart)->format('Y-m-d'),
                ]);
            } else {
                $request->merge([
                    'job_datestart' => Carbon::createFromFormat('d/m/y', $request->job_datestart)->format('Y-m-d'),
                ]);
            }
        }

        // Normalize time fields
        if ($request->job_time_start) {
            $request->merge([
                'job_time_start' => Carbon::parse($request->job_time_start)->format('H:i'),
            ]);
        }

        if ($request->job_time_end) {
            $request->merge([
                'job_time_end' => Carbon::parse($request->job_time_end)->format('H:i'),
            ]);
        }

        // ⭐ INCLUDE NEW FIELDS HERE:
        $original = $job->only([
            'job_type',
            'job_datestart',
            'job_time_start',
            'job_time_end',
            'direction',
            'job_sitNumber',
            'job_remarks',
            'driver_name',
            'conductor_name',
        ]);

        // Update database
        $job->update($request->only([
            'job_type',
            'job_datestart',
            'job_time_start',
            'job_time_end',
            'direction',
            'job_sitNumber',
            'job_remarks',
            'driver_name',
            'conductor_name',
        ]));

        // Detect changes for logs
        $changes = [];
        foreach ($original as $field => $oldValue) {
            $newValue = $job->$field;

            if ($oldValue != $newValue) {
                $changes[$field] = [
                    'old' => $oldValue ?? 'None',
                    'new' => $newValue ?? 'None',
                ];
            }
        }

        // Create log if changed
        if (! empty($changes)) {
            JobOrderLog::create([
                'joborder_id' => $job->id,
                'user_id' => Auth::id(),
                'action' => 'updated details',
                'meta' => $changes,
            ]);
        }

        flash('Job details updated successfully.')->success();

        return back();
    }
}
