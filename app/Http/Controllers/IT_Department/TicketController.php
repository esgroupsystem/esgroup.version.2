<?php

namespace App\Http\Controllers\IT_Department;

use App\Events\JobOrderCreated;
use App\Exports\JobOrdersExport;
use App\Helpers\Notifier;
use App\Http\Controllers\Controller;
use App\Http\Requests\ITDepartment\StoreJobOrderRequest;
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
use Maatwebsite\Excel\Facades\Excel;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');
        $search = trim((string) $request->get('search', ''));

        // Base query with optional search
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

        // Handle AJAX pagination per tab
        if ($request->ajax()) {
            $list = (clone $baseQuery)
                ->when($tab === 'pending', fn ($q) => $q->whereIn('job_status', ['Pending', 'Approval']))
                ->when($tab === 'progress', fn ($q) => $q->where('job_status', 'In Progress'))
                ->when($tab === 'completed', fn ($q) => $q->where('job_status', 'Completed'))
                ->paginate(10)
                ->withQueryString(); // preserve tab & search in pagination links

            return view('it_department.people-table', compact('list', 'tab'))->render();
        }

        // Prepare tab-specific paginations
        $pending = (clone $baseQuery)
            ->whereIn('job_status', ['Pending', 'Approval'])
            ->paginate(10, ['*'], 'pending_page')
            ->appends(['tab' => 'pending']);

        $progress = (clone $baseQuery)
            ->where('job_status', 'In Progress')
            ->paginate(10, ['*'], 'progress_page')
            ->appends(['tab' => 'progress']);

        $completed = (clone $baseQuery)
            ->where('job_status', 'Completed')
            ->paginate(10, ['*'], 'completed_page')
            ->appends(['tab' => 'completed']);

        // Status counts
        $statusCounts = JobOrder::selectRaw("
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as new_count,
            SUM(CASE WHEN job_status IN ('Pending', 'Approval') THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN job_status = 'In Progress' THEN 1 ELSE 0 END) as progress_count,
            SUM(CASE WHEN job_status = 'Completed' THEN 1 ELSE 0 END) as completed_count
        ")->first();

        $stats = [
            'new' => (int) ($statusCounts->new_count ?? 0),
            'pending' => (int) ($statusCounts->pending_count ?? 0),
            'progress' => (int) ($statusCounts->progress_count ?? 0),
            'completed' => (int) ($statusCounts->completed_count ?? 0),
        ];

        // Categories
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

        // Agents
        $agents = User::whereIn('role', ['IT Head', 'IT Officer', 'IT Technician'])
            ->withCount('jobOrdersAssigned')
            ->orderBy('full_name')
            ->get();

        return view('it_department.ticket_job_order', compact(
            'pending', 'progress', 'completed', 'stats', 'categories', 'agents', 'tab'
        ));
    }

    public function approve($id)
    {
        $job = JobOrder::findOrFail($id);

        abort_unless(in_array(Auth::user()->role, ['IT Head', 'Developer']), 403);

        /*
        |--------------------------------------------------------------------------
        | Approval Validation
        |--------------------------------------------------------------------------
        | Allow approval when approval_status is still "Approval",
        | even if job_status is already "Pending".
        */
        if ($job->approval_status !== 'Approval') {
            return redirect()
                ->route('tickets.joborder.index')
                ->with(
                    'warning',
                    "This job order is not waiting for approval. Current status: {$job->job_status} / {$job->approval_status}"
                );
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
            'meta' => [
                'message' => 'Approved by IT Head',
                'job_status' => 'Pending',
                'approval_status' => 'Approved',
            ],
        ]);

        return redirect()
            ->route('tickets.joborder.index')
            ->with('success', 'Job order approved successfully.');
    }

    public function disapprove($id)
    {
        $job = JobOrder::findOrFail($id);

        abort_unless(in_array(Auth::user()->role, ['IT Head', 'Developer']), 403);

        if ($job->approval_status !== 'Approval') {
            return redirect()
                ->route('tickets.joborder.index')
                ->with(
                    'warning',
                    "This job order is not waiting for approval. Current status: {$job->job_status} / {$job->approval_status}"
                );
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
            'meta' => [
                'message' => 'Disapproved by IT Head',
                'job_status' => 'Disapproved',
                'approval_status' => 'Disapproved',
            ],
        ]);

        return redirect()
            ->route('tickets.joborder.index')
            ->with('error', 'Job order disapproved.');
    }

    public function cctvindex()
    {
        return view('it_department.concern.index');
    }

    public function createjobordersIndex()
    {
        $buses = BusDetail::query()
            ->select([
                'id',
                'garage',
                'name',
                'body_number',
                'plate_number',
            ])
            ->orderBy('body_number')
            ->orderBy('plate_number')
            ->get();

        return view(
            'it_department.create_joborder',
            compact('buses')
        );
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

    public function storeJoborders(StoreJobOrderRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        try {
            $job = DB::transaction(function () use (
                $validated,
                $request,
                $user
            ): JobOrder {
                $bus = BusDetail::query()
                    ->whereKey($validated['bus_detail_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $bus) {
                    throw new \RuntimeException(
                        'The selected bus no longer exists.'
                    );
                }

                $incidentDate = Carbon::createFromFormat(
                    'd/m/y',
                    $validated['job_datestart']
                )->format('Y-m-d');

                $job = JobOrder::create([
                    'bus_detail_id' => $bus->id,
                    'created_by' => $user->id,

                    'job_name' => $validated['job_name'] ?? 'Job Order',
                    'job_type' => $validated['job_type'],
                    'job_datestart' => $incidentDate,
                    'job_time_start' => $validated['job_time_start'],
                    'job_time_end' => $validated['job_time_end'],

                    'job_sitNumber' => $validated['job_sitNumber'] ?? null,
                    'job_remarks' => $validated['job_remarks'] ?? null,

                    'approval_status' => 'Approval',
                    'job_status' => 'Approval',

                    'job_assign_person' => null,
                    'job_date_filled' => now(),
                    'job_creator' => $user->full_name
                        ?? $user->name
                        ?? $user->username
                        ?? 'System',

                    'driver_name' => $validated['driver_name'] ?? null,
                    'conductor_name' => $validated['conductor_name'] ?? null,
                    'direction' => $validated['direction'] ?? null,
                ]);

                foreach ($request->file('files', []) as $upload) {
                    if (! $upload->isValid()) {
                        continue;
                    }

                    $storedPath = $upload->store(
                        "joborders/{$job->id}",
                        'public'
                    );

                    if (
                        ! $storedPath
                        || ! Storage::disk('public')->exists($storedPath)
                    ) {
                        throw new \RuntimeException(
                            "Failed to save attachment: {$upload->getClientOriginalName()}"
                        );
                    }

                    JobOrderFile::create([
                        'job_id' => $job->id,
                        'file_name' => $upload->getClientOriginalName(),
                        'file_remarks' => null,
                        'file_notes' => null,
                        'file_path' => $storedPath,
                    ]);
                }

                JobOrderLog::create([
                    'joborder_id' => $job->id,
                    'action' => 'created',
                    'meta' => [
                        'job_type' => $job->job_type,
                        'status' => $job->job_status,
                        'bus_detail_id' => $bus->id,
                        'body_number' => $bus->body_number,
                        'plate_number' => $bus->plate_number,
                    ],
                    'user_id' => $user->id,
                ]);

                Log::info('Job order database transaction completed.', [
                    'job_order_id' => $job->id,
                    'bus_detail_id' => $bus->id,
                    'created_by' => $user->id,
                ]);

                return $job->load('bus');
            }, 3);
        } catch (Throwable $exception) {
            Log::error('Job Order Creation Error', [
                'route' => $request->route()?->getName(),
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'bus_detail_id' => $validated['bus_detail_id'] ?? null,
                'user_id' => $user?->id,
                'input' => $request->except([
                    '_token',
                    'files',
                ]),
            ]);

            flash('Something went wrong while creating the job order.')
                ->error();

            return back()
                ->withInput()
                ->withErrors([
                    'job_order' => app()->environment('local')
                        ? $exception->getMessage()
                        : 'Unable to create the job order.',
                ]);
        }

        /*
         * These actions occur only after the job order transaction
         * has committed successfully.
         */

        try {
            event(new JobOrderCreated($job));
        } catch (Throwable $exception) {
            Log::error('Job-order database notification failed.', [
                'job_order_id' => $job->id,
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);
        }

        try {
            Notifier::notifyRoles(
                [
                    'IT Head',
                    'IT Officer',
                ],
                new JobOrderCreatedMail($job)
            );
        } catch (Throwable $exception) {
            /*
             * Email queue failure must not invalidate or delete
             * an already-created job order.
             */
            Log::error('Job-order email queueing failed.', [
                'job_order_id' => $job->id,
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);
        }

        flash("Job Order #{$job->id} created successfully!")
            ->success();

        return redirect()->route('tickets.joborder.index');
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
