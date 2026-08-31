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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        $tab = (string) $request->input('tab', 'pending');
        $search = trim((string) $request->input('search', ''));

        if ($request->ajax()) {
            $list = $this->directoryService->paginateTab($tab, $search);

            return view('it_department.people-table', compact('list', 'tab'))->render();
        }

        return view('it_department.ticket_job_order', [
            ...$this->directoryService->indexData($search),
            'tab' => $tab,
        ]);
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
