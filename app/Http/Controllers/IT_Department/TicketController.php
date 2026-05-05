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
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');
        $search = trim((string) $request->get('search', ''));

        $baseQuery = JobOrder::with('bus')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('job_creator', 'like', "%{$search}%")
                        ->orWhere('job_type', 'like', "%{$search}%")
                        ->orWhere('job_status', 'like', "%{$search}%")
                        ->orWhere('driver_name', 'like', "%{$search}%")
                        ->orWhere('conductor_name', 'like', "%{$search}%")
                        ->orWhereHas('bus', function ($bus) use ($search) {
                            $bus->where('body_number', 'like', "%{$search}%")
                                ->orWhere('plate_number', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('job_date_filled');

        if ($request->ajax()) {
            $list = (clone $baseQuery)
                ->when($tab === 'pending', fn ($q) => $q->whereIn('job_status', ['Pending', 'Approval']))
                ->when($tab === 'progress', fn ($q) => $q->where('job_status', 'In Progress'))
                ->when($tab === 'completed', fn ($q) => $q->where('job_status', 'Completed'))
                ->paginate(10);

            return view('tickets.partials.table', compact('list'))->render();
        }

        $pending = JobOrder::with('bus')
            ->whereIn('job_status', ['Pending', 'Approval'])
            ->orderByDesc('job_date_filled')
            ->paginate(10, ['*'], 'pending_page');

        $progress = JobOrder::with('bus')
            ->where('job_status', 'In Progress')
            ->orderByDesc('job_date_filled')
            ->paginate(10, ['*'], 'progress_page');

        $completed = JobOrder::with('bus')
            ->where('job_status', 'Completed')
            ->orderByDesc('job_date_filled')
            ->paginate(10, ['*'], 'completed_page');

        $statusCounts = JobOrder::selectRaw("
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as new_count,
                SUM(CASE WHEN job_status IN ('Pending', 'Approval') THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN job_status = 'In Progress' THEN 1 ELSE 0 END) as progress_count,
                SUM(CASE WHEN job_status = 'Completed' THEN 1 ELSE 0 END) as completed_count
            ")
            ->first();

        $stats = [
            'new' => (int) ($statusCounts->new_count ?? 0),
            'pending' => (int) ($statusCounts->pending_count ?? 0),
            'progress' => (int) ($statusCounts->progress_count ?? 0),
            'completed' => (int) ($statusCounts->completed_count ?? 0),
        ];

        $categoryList = [
            'ACCIDENT', 'COLLECTING FARE', 'CUTTING FARE', 'RE- ISSUEING TICKET',
            'TAMPERING TICKET', 'UNREGISTERED TICKET', 'DELAYING ISSUANCE OF TICKET',
            'ROLLING TICKETS', 'REMOVING HEADSTAB OF TICKET', 'USING STUB TICKET',
            'WRONG CLOSING / OPEN', 'OTHERS',
        ];

        $categoryCounts = JobOrder::query()
            ->select('job_type')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('job_type')
            ->pluck('total', 'job_type');

        $categories = collect($categoryList)
            ->map(fn ($cat) => [
                'name' => $cat,
                'total' => (int) ($categoryCounts[$cat] ?? 0),
            ])
            ->values()
            ->all();

        $agents = User::whereIn('role', ['IT Head', 'IT Officer', 'IT Technician'])
            ->withCount('jobOrdersAssigned')
            ->orderBy('full_name')
            ->get();

        return view('it_department.ticket_job_order', compact(
            'pending',
            'progress',
            'completed',
            'stats',
            'categories',
            'agents'
        ));
    }

    public function approve($id)
    {
        $job = JobOrder::findOrFail($id);

        abort_unless(in_array(Auth::user()->role, ['IT Head', 'Developer']), 403);

        if ($job->job_status !== 'Approval' || $job->approval_status !== 'Approval') {
            return back();
        }

        $job->update([
            'approval_status' => 'Approved',
            'job_status' => 'Pending',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        JobOrderLog::create([
            'joborder_id' => $job->id,
            'user_id' => Auth::id(),
            'action' => 'approved',
            'meta' => ['message' => 'Approved by IT Head'],
        ]);

        return back()->with('success', 'Job approved');
    }

    public function disapprove($id)
    {
        $job = JobOrder::findOrFail($id);

        abort_unless(in_array(Auth::user()->role, ['IT Head', 'Developer']), 403);

        if ($job->job_status !== 'Approval' || $job->approval_status !== 'Approval') {
            return back();
        }

        $job->update([
            'approval_status' => 'Disapproved',
            'job_status' => 'Disapproved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        JobOrderLog::create([
            'joborder_id' => $job->id,
            'user_id' => Auth::id(),
            'action' => 'disapproved',
            'meta' => ['message' => 'Disapproved by IT Head'],
        ]);

        return back()->with('error', 'Job disapproved');
    }

    public function cctvindex()
    {
        return view('it_department.concern.index');
    }

    public function createjobordersIndex()
    {
        $buses = BusDetail::orderBy('body_number')->get();

        return view('it_department.create_joborder', compact('buses'));
    }

    public function view($id)
    {
        $job = JobOrder::with([
            'bus',
            'files',
            'logs.user',
            'notes.user',
        ])->findOrFail($id);

        JobOrderLog::updateOrCreate(
            [
                'joborder_id' => $job->id,
                'user_id' => Auth::id(),
                'action' => 'viewed',
            ],
            [
                'meta' => ['message' => 'User viewed the job order details'],
            ]
        );

        $logs = JobOrderLog::with('user')
            ->where('joborder_id', $job->id)
            ->orderByDesc('created_at')
            ->get();

        return view('it_department.view_joborder', compact('job', 'logs'));
    }

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
                'job_status' => ['nullable'],
                'job_assign_person' => ['nullable', 'string', 'max:255'],
                'direction' => ['nullable', 'string', 'max:255'],
                'driver_name' => ['nullable', 'string', 'max:255'],
                'conductor_name' => ['nullable', 'string', 'max:255'],
                'files.*' => ['required', 'file', 'max:1024000'],
            ]);

            $user = Auth::user();

            return DB::transaction(function () use ($validated, $request, $user) {
                $bus = BusDetail::where('body_number', $validated['body_number'])->first();

                if (! $bus) {
                    flash('Selected bus does not exist.')->error();

                    return back()->withInput();
                }

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
                    'approval_status' => 'Approval',
                    'job_status' => 'Approval',
                    'job_assign_person' => null,
                    'job_date_filled' => now(),
                    'job_creator' => optional($user)->full_name,
                    'driver_name' => $validated['driver_name'] ?? null,
                    'conductor_name' => $validated['conductor_name'] ?? null,
                    'direction' => $validated['direction'] ?? null,
                ]);

                foreach ($request->file('files', []) as $upload) {
                    if (! $upload->isValid()) {
                        continue;
                    }

                    $stored = $upload->store("joborders/{$job->id}", 'public');

                    if (! $stored || ! Storage::disk('public')->exists($stored)) {
                        continue;
                    }

                    JobOrderFile::create([
                        'job_id' => $job->id,
                        'file_name' => $upload->getClientOriginalName(),
                        'file_remarks' => null,
                        'file_notes' => null,
                        'file_path' => $stored,
                    ]);
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

                $job->load('bus');

                event(new JobOrderCreated($job));

                Notifier::notifyRoles(
                    ['IT Head', 'IT Officer'],
                    new JobOrderCreatedMail($job)
                );

                flash("Job Order #{$job->id} created successfully!")->success();

                return redirect()->route('tickets.joborder.index');
            });
        } catch (ValidationException $e) {
            Log::warning('Job Order Validation Failed', [
                'route' => optional(request()->route())->getName(),
                'errors' => $e->validator->errors()->toArray(),
                'input' => request()->except(['files']),
            ]);

            flash('Validation failed. Please check the fields.')->error();

            return back()->withErrors($e->validator)->withInput();
        } catch (\Throwable $e) {
            Log::error('Job Order Creation Error', [
                'route' => optional(request()->route())->getName(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'input' => request()->except(['files']),
            ]);

            flash('Something went wrong while creating the job order.')->error();

            return back()
                ->with('debug', app()->environment('local') ? $e->getMessage() : null)
                ->withInput();
        }
    }

    public function destroy($id)
    {
        abort_unless(in_array(Auth::user()->role, ['IT Head', 'Developer']), 403);

        $job = JobOrder::with('files')->findOrFail($id);

        if (in_array($job->job_status, ['In Progress', 'Completed'])) {
            flash('You cannot delete a job order that is In Progress or Completed.')->error();

            return back();
        }

        return DB::transaction(function () use ($job) {
            foreach ($job->files as $file) {
                if ($file->file_path) {
                    Storage::disk('public')->delete($file->file_path);
                }
            }

            Storage::disk('public')->deleteDirectory("joborders/{$job->id}");

            JobOrderFile::where('job_id', $job->id)->delete();
            JobOrderNote::where('joborder_id', $job->id)->delete();
            JobOrderLog::where('joborder_id', $job->id)->delete();

            $jobId = $job->id;
            $job->delete();

            flash("Job Order #{$jobId} deleted successfully.")->success();

            return back();
        });
    }

    public function addNote(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string'],
            'details' => ['nullable', 'string'],
        ]);

        JobOrder::findOrFail($id);

        JobOrderNote::create([
            'joborder_id' => $id,
            'user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
        ]);

        JobOrderLog::create([
            'joborder_id' => $id,
            'user_id' => Auth::id(),
            'action' => 'added note',
            'meta' => ['reason' => $validated['reason']],
        ]);

        return back();
    }

    public function addFiles(Request $request, $id)
    {
        $job = JobOrder::findOrFail($id);

        $request->validate([
            'files.*' => ['required', 'file', 'max:1024000'],
        ]);

        $files = $request->file('files', []);

        foreach ($files as $upload) {
            if (! $upload->isValid()) {
                continue;
            }

            $stored = $upload->store("joborders/{$job->id}", 'public');

            if (! $stored || ! Storage::disk('public')->exists($stored)) {
                continue;
            }

            JobOrderFile::create([
                'job_id' => $job->id,
                'file_name' => $upload->getClientOriginalName(),
                'file_path' => $stored,
            ]);
        }

        if (count($files) > 0) {
            JobOrderLog::create([
                'joborder_id' => $job->id,
                'user_id' => Auth::id(),
                'action' => 'added file',
                'meta' => ['file_count' => count($files)],
            ]);
        }

        flash('Files uploaded successfully.')->info();

        return back();
    }

    public function export($type)
    {
        if ($type === 'excel') {
            return Excel::download(new JobOrdersExport, 'job_orders.xlsx');
        }

        if ($type === 'pdf') {
            $data = JobOrder::with('bus')
                ->orderByDesc('job_date_filled')
                ->get();

            $pdf = Pdf::loadView('it_department.export.pdf', compact('data'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('job_orders.pdf');
        }

        return back()->with('error', 'Invalid export type selected.');
    }

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

        if ($request->filled('job_datestart')) {
            $request->merge([
                'job_datestart' => $this->normalizeDate($request->job_datestart),
            ]);
        }

        if ($request->filled('job_time_start')) {
            $request->merge([
                'job_time_start' => Carbon::parse($request->job_time_start)->format('H:i'),
            ]);
        }

        if ($request->filled('job_time_end')) {
            $request->merge([
                'job_time_end' => Carbon::parse($request->job_time_end)->format('H:i'),
            ]);
        }

        $fields = [
            'job_type',
            'job_datestart',
            'job_time_start',
            'job_time_end',
            'direction',
            'job_sitNumber',
            'job_remarks',
            'driver_name',
            'conductor_name',
        ];

        $original = $job->only($fields);

        $job->update($request->only($fields));

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

    public function print($id)
    {
        $job = JobOrder::with('bus')->findOrFail($id);

        return view('it_department.print.joborder', compact('job'));
    }

    private function normalizeDate(string $date): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return Carbon::parse($date)->format('Y-m-d');
        }

        return Carbon::createFromFormat('d/m/y', $date)->format('Y-m-d');
    }
}
