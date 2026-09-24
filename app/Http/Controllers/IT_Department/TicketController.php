<?php

declare(strict_types=1);

namespace App\Http\Controllers\IT_Department;

use App\Exports\JobOrdersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ITDepartment\AddJobOrderFilesRequest;
use App\Http\Requests\ITDepartment\AddJobOrderNoteRequest;
use App\Http\Requests\ITDepartment\StoreJobOrderRequest;
use App\Http\Requests\ITDepartment\UpdateJobOrderRequest;
use App\Models\BusDetail;
use App\Models\JobOrder;
use App\Models\JobOrderLog;
use App\Models\User;
use App\Services\ITDepartment\ItJobOrderDirectoryService;
use App\Services\ITDepartment\ItJobOrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

final class TicketController extends Controller
{
    public function __construct(
        private readonly ItJobOrderService $jobOrderService,
        private readonly ItJobOrderDirectoryService $directoryService,
    ) {}

    public function index(Request $request)
    {
        $tab = in_array($request->input('tab'), ['pending', 'progress', 'completed'], true)
            ? (string) $request->input('tab')
            : 'pending';
        $search = trim((string) $request->input('search', ''));
        $user = $request->user();
        $isApprover = $user instanceof User
            && $user->can('tickets.approve')
            && ($user->isDeveloper() || $user->hasAnyRole(['IT Head', 'Developer']));

        return Inertia::render('it/job-orders/index', [
            'tickets' => $this->directoryService->paginateTab($tab, $search)
                ->through(fn (JobOrder $job): array => $this->ticketRow($job, $isApprover, $user)),
            'stats' => $this->directoryService->stats(),
            'filters' => ['tab' => $tab, 'search' => $search],
            'can' => [
                'create' => (bool) $user?->can('tickets.create'),
                'export' => (bool) $user?->can('tickets.export'),
            ],
            'urls' => [
                'index' => route('tickets.joborder.index'),
                'create' => route('tickets.createjoborder.index'),
                'exportPdf' => route('tickets.export', 'pdf'),
                'exportExcel' => route('tickets.export', 'excel'),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function ticketRow(JobOrder $job, bool $isApprover, ?User $user): array
    {
        $status = strtolower(trim(str_replace(['_', '-'], ' ', (string) ($job->job_status ?? 'Pending'))));
        $label = match (true) {
            in_array($status, ['approval', 'pending'], true) => 'Pending',
            in_array($status, ['disapproved', 'rejected', 'reject'], true) => 'Rejected',
            $status === 'in progress' => 'In Progress',
            $status === 'completed' => 'Completed',
            default => ucwords((string) $job->job_status),
        };
        $date = $job->job_date_filled ?? $job->created_at;

        return [
            'id' => $job->id,
            'bus' => trim(($job->bus?->name ?? 'ES Transport').(($job->bus?->body_number ?? $job->bus?->plate_number) ? ' - '.($job->bus?->body_number ?? $job->bus?->plate_number) : '')),
            'requester' => $job->job_creator ?: 'System',
            'issue' => strtoupper((string) ($job->job_type ?: 'General')),
            'seat' => filled($job->job_sitNumber) ? (string) $job->job_sitNumber : null,
            'status' => $status,
            'status_label' => $label,
            'date' => $date ? Carbon::parse($date)->format('Y-m-d') : '-',
            'actions' => [
                'approve' => $status === 'approval' && $isApprover,
                'view' => in_array($status, ['pending', 'in progress', 'completed'], true) && (bool) $user?->can('tickets.view'),
                'delete' => in_array($status, ['pending', 'disapproved', 'rejected', 'reject'], true)
                    && (bool) $user?->can('tickets.delete')
                    && ($user?->isDeveloper() || $user?->hasAnyRole(['IT Head', 'Developer'])),
            ],
            'urls' => [
                'view' => route('tickets.joborder.view', $job->id),
                'approve' => route('tickets.approve', $job->id),
                'disapprove' => route('tickets.disapprove', $job->id),
                'delete' => route('tickets.joborder.delete', $job->id),
            ],
        ];
    }

    public function approve(int $id)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->hasAnyRole(['IT Head', 'Developer']), 403);

        $job = JobOrder::query()->findOrFail($id);
        if (! $this->jobOrderService->approve($job, $user)) {
            return redirect()
                ->route('tickets.joborder.index')
                ->with('warning', "This job order is not waiting for approval. Current status: {$job->job_status} / {$job->approval_status}");
        }

        return redirect()
            ->route('tickets.joborder.index')
            ->with('success', 'Job order approved successfully.');
    }

    public function disapprove(int $id)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->hasAnyRole(['IT Head', 'Developer']), 403);

        $job = JobOrder::query()->findOrFail($id);
        if (! $this->jobOrderService->disapprove($job, $user)) {
            return redirect()
                ->route('tickets.joborder.index')
                ->with('warning', "This job order is not waiting for approval. Current status: {$job->job_status} / {$job->approval_status}");
        }

        return redirect()
            ->route('tickets.joborder.index')
            ->with('error', 'Job order disapproved.');
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

        return Inertia::render('it/job-orders/create', [
            'buses' => $buses->map(fn (BusDetail $bus): array => [
                'value' => (string) $bus->id,
                'label' => ($bus->body_number ?: 'No Body Number').' — '.($bus->plate_number ?: 'No Plate Number'),
                'hint' => ($bus->name ?: 'No Bus Name').' — '.($bus->garage ?: 'No Garage'),
            ])->values(),
            'issueTypes' => ItJobOrderDirectoryService::CATEGORIES,
            'reporter' => (string) (Auth::user()?->full_name ?? ''),
            'seatLayout' => asset('assets/img/bus/seat_arrangement.png'),
            'urls' => [
                'index' => route('tickets.joborder.index'),
                'store' => route('tickets.storejoborder.post'),
            ],
        ]);
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

        $format = static function ($value, string $pattern): ?string {
            try {
                return $value ? Carbon::parse($value)->format($pattern) : null;
            } catch (Throwable) {
                return is_string($value) ? $value : null;
            }
        };
        $scalar = static fn ($value): string => is_scalar($value) || $value === null ? (string) ($value ?? 'N/A') : (string) json_encode($value);
        $user = Auth::user();

        return Inertia::render('it/job-orders/show', [
            'job' => [
                'id' => $job->id,
                'number' => str_pad((string) $job->id, 5, '0', STR_PAD_LEFT),
                'status' => (string) $job->job_status,
                'job_type' => $job->job_type,
                'creator' => $job->job_creator ?: 'System',
                'created_at' => $job->created_at?->format('M d, Y h:i A'),
                'direction' => $job->direction,
                'date_label' => $format($job->job_datestart, 'F d, Y') ?? 'N/A',
                'time_start_label' => $format($job->job_time_start, 'h:i A') ?? 'N/A',
                'time_end_label' => $format($job->job_time_end, 'h:i A') ?? 'N/A',
                'seat' => $job->job_sitNumber,
                'assigned_to' => $job->job_assign_person,
                'remarks' => $job->job_remarks,
                'driver_name' => $job->driver_name,
                'conductor_name' => $job->conductor_name,
                'bus' => $job->bus ? [
                    'name' => $job->bus->name,
                    'body_number' => $job->bus->body_number,
                    'plate_number' => $job->bus->plate_number,
                    'garage' => $job->bus->garage,
                ] : null,
            ],
            'values' => [
                'job_type' => (string) ($job->job_type ?? ''),
                'job_datestart' => $format($job->job_datestart, 'Y-m-d') ?? '',
                'job_time_start' => $format($job->job_time_start, 'H:i') ?? '',
                'job_time_end' => $format($job->job_time_end, 'H:i') ?? '',
                'direction' => (string) ($job->direction ?? ''),
                'job_sitNumber' => $job->job_sitNumber !== null ? (string) $job->job_sitNumber : '',
                'job_remarks' => (string) ($job->job_remarks ?? ''),
                'driver_name' => (string) ($job->driver_name ?? ''),
                'conductor_name' => (string) ($job->conductor_name ?? ''),
            ],
            'files' => $job->files->map(function ($file) use ($job): array {
                $name = $file->file_name ?: basename((string) $file->file_path);

                return [
                    'id' => $file->id,
                    'name' => $name,
                    'extension' => strtolower(pathinfo($name, PATHINFO_EXTENSION)),
                    'url' => route('tickets.joborder.file.download', [$job->id, $file->id]),
                ];
            })->values(),
            'notes' => $job->notes->map(fn ($note): array => [
                'id' => $note->id,
                'reason' => $note->reason,
                'details' => $note->details,
                'user' => $note->user?->full_name ?? 'System',
                'date' => $note->created_at?->format('M d, Y'),
                'time' => $note->created_at?->format('h:i A'),
            ])->values(),
            'logs' => $logs->map(function (JobOrderLog $log) use ($scalar): array {
                $meta = is_string($log->meta) ? (json_decode($log->meta, true) ?: []) : (is_array($log->meta) ? $log->meta : []);

                return [
                    'id' => $log->id,
                    'user' => $log->user?->full_name ?? 'System',
                    'action' => ucfirst(str_replace('_', ' ', (string) $log->action)),
                    'date' => $log->created_at?->format('M d, Y'),
                    'time' => $log->created_at?->format('h:i A'),
                    'meta' => collect($meta)->map(fn ($value, $key): array => [
                        'key' => ucfirst(str_replace('_', ' ', (string) $key)),
                        'old' => is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value) ? $scalar($value['old']) : null,
                        'new' => is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value) ? $scalar($value['new']) : null,
                        'value' => is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value) ? null : $scalar($value),
                    ])->values(),
                ];
            })->values(),
            'issueTypes' => ItJobOrderDirectoryService::CATEGORIES,
            'noteReasons' => ['Defective DVR', 'Camera not working', 'Weak signal / interference', 'Other'],
            'can' => ['update' => (bool) $user?->can('tickets.update')],
            'urls' => [
                'index' => route('tickets.joborder.index'),
                'print' => route('tickets.joborder.print', $job->id),
                'update' => route('tickets.joborder.update', $job->id),
                'accept' => route('tickets.joborder.accept', $job->id),
                'done' => route('tickets.joborder.done', $job->id),
                'addNote' => route('tickets.joborder.addnote', $job->id),
                'addFiles' => route('tickets.joborder.addfile', $job->id),
            ],
        ]);
    }

    public function storeJoborders(StoreJobOrderRequest $request)
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        try {
            $job = $this->jobOrderService->create(
                $request->validated(),
                $request->file('files', []),
                $user,
            );
        } catch (Throwable $exception) {
            Log::error('Job Order Creation Error', [
                'route' => $request->route()?->getName(),
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
                'bus_detail_id' => $request->validated('bus_detail_id'),
                'user_id' => $user->id,
            ]);

            flash('Something went wrong while creating the job order.')->error();

            return back()
                ->withInput()
                ->withErrors([
                    'job_order' => app()->environment('local')
                        ? $exception->getMessage()
                        : 'Unable to create the job order.',
                ]);
        }

        flash("Job Order #{$job->id} created successfully!")->success();

        return redirect()->route('tickets.joborder.index');
    }

    public function destroy(int $id)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->hasAnyRole(['IT Head', 'Developer']), 403);

        $job = JobOrder::query()->findOrFail($id);
        if (! $this->jobOrderService->delete($job)) {
            flash('You cannot delete a job order that is In Progress or Completed.')->error();

            return back();
        }

        flash("Job Order #{$id} deleted successfully.")->success();

        return back();
    }

    public function addNote(AddJobOrderNoteRequest $request, int $id)
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $job = JobOrder::query()->findOrFail($id);
        $this->jobOrderService->addNote($job, $request->validated(), $user);

        return back();
    }

    public function addFiles(AddJobOrderFilesRequest $request, int $id)
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $job = JobOrder::query()->findOrFail($id);
        $this->jobOrderService->addFiles($job, $request->file('files', []), $user);

        flash('Files uploaded successfully.')->info();

        return back();
    }

    public function downloadFile(int $id, int $file): BinaryFileResponse
    {
        $job = JobOrder::findOrFail($id);
        $jobFile = $job->files()->findOrFail($file);

        $downloadName = $jobFile->file_name ?: basename($jobFile->file_path);

        abort_unless(Storage::disk('local')->exists($jobFile->file_path), 404);

        $disk = Storage::disk('local');

        return response()->download($disk->path($jobFile->file_path), $downloadName);
    }

    public function export($type)
    {
        if ($type === 'excel') {
            return Excel::download(new JobOrdersExport, 'job_orders.xlsx');
        }

        if ($type === 'pdf') {
            $data = JobOrder::with('bus')
                ->orderByDesc('job_date_filled')
                ->limit(5000)
                ->get();

            $pdf = Pdf::loadView('it_department.export.pdf', compact('data'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('job_orders.pdf');
        }

        return back()->with('error', 'Invalid export type selected.');
    }

    public function acceptTask(int $id)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $this->jobOrderService->accept(JobOrder::query()->findOrFail($id), $user);

        return back();
    }

    public function markAsDone(int $id)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $this->jobOrderService->complete(JobOrder::query()->findOrFail($id), $user);

        return back();
    }

    public function update(UpdateJobOrderRequest $request, int $id)
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $job = JobOrder::query()->findOrFail($id);
        $this->jobOrderService->update($job, $request->validated(), $user);

        flash('Job details updated successfully.')->success();

        return back();
    }

    public function print($id)
    {
        $job = JobOrder::with('bus')->findOrFail($id);

        return view('it_department.print.joborder', compact('job'));
    }
}
