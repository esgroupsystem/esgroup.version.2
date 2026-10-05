<?php

declare(strict_types=1);

namespace App\Http\Controllers\IT;

use App\Exports\JobOrdersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\IT\AddJobOrderFilesRequest;
use App\Http\Requests\IT\AddJobOrderNoteRequest;
use App\Http\Requests\IT\StoreJobOrderRequest;
use App\Http\Requests\IT\UpdateJobOrderRequest;
use App\Http\Resources\IT\JobOrderDetailResource;
use App\Http\Resources\IT\JobOrderLogResource;
use App\Http\Resources\IT\JobOrderPrintResource;
use App\Http\Resources\IT\JobOrderRowResource;
use App\Models\BusDetail;
use App\Models\JobOrder;
use App\Models\User;
use App\Services\IT\JobOrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

/**
 * IT Support → Tickets Job Order.
 */
final class TicketController extends Controller
{
    public function __construct(
        private readonly JobOrderService $jobOrders,
    ) {}

    public function index(Request $request): Response
    {
        $tab = in_array($request->input('tab'), JobOrderService::TABS, true) ? (string) $request->input('tab') : 'pending';
        $search = trim((string) $request->input('search', ''));
        $user = $request->user();

        return Inertia::render('it/job-orders/index', [
            'tickets' => $this->jobOrders->paginateTab($tab, $search)
                ->through(fn (JobOrder $job): array => JobOrderRowResource::make($job)->resolve($request)),
            'stats' => $this->jobOrders->stats(),
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

    public function createjobordersIndex(Request $request): Response
    {
        return Inertia::render('it/job-orders/create', [
            'buses' => $this->jobOrders->busOptions()->map(fn (BusDetail $bus): array => [
                'value' => (string) $bus->id,
                'label' => ($bus->body_number ?: 'No Body Number').' — '.($bus->plate_number ?: 'No Plate Number'),
                'hint' => ($bus->name ?: 'No Bus Name').' — '.($bus->garage ?: 'No Garage'),
            ])->values(),
            'issueTypes' => JobOrder::CATEGORIES,
            'reporter' => (string) ($request->user()?->full_name ?? ''),
            'urls' => [
                'index' => route('tickets.joborder.index'),
                'store' => route('tickets.storejoborder.post'),
            ],
        ]);
    }

    public function storeJoborders(StoreJobOrderRequest $request): RedirectResponse
    {
        $user = $this->actor($request);

        try {
            $job = $this->jobOrders->create($request->validated(), $request->file('files', []), $user);
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
                    'job_order' => app()->environment('local') ? $exception->getMessage() : 'Unable to create the job order.',
                ]);
        }

        flash("Job Order #{$job->id} created successfully!")->success();

        return redirect()->route('tickets.joborder.index');
    }

    public function view(Request $request, int $id): Response
    {
        ['job' => $job, 'logs' => $logs] = $this->jobOrders->openForViewer($id, $this->actor($request));

        return Inertia::render('it/job-orders/show', [
            ...JobOrderDetailResource::make($job)->resolve($request),
            'logs' => JobOrderLogResource::collection($logs)->resolve($request),
            'issueTypes' => JobOrder::CATEGORIES,
            'noteReasons' => JobOrder::NOTE_REASONS,
            'can' => ['update' => (bool) $request->user()?->can('tickets.update')],
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

    public function update(UpdateJobOrderRequest $request, int $id): RedirectResponse
    {
        $this->jobOrders->update($this->jobOrders->find($id), $request->validated(), $this->actor($request));

        flash('Job details updated successfully.')->success();

        return back();
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->abortUnlessItHead($request);

        if (! $this->jobOrders->delete($this->jobOrders->find($id))) {
            flash('You cannot delete a job order that is In Progress or Completed.')->error();

            return back();
        }

        flash("Job Order #{$id} deleted successfully.")->success();

        return back();
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $user = $this->abortUnlessItHead($request);
        $job = $this->jobOrders->find($id);

        if (! $this->jobOrders->approve($job, $user)) {
            return $this->notWaitingForApproval($job);
        }

        return redirect()->route('tickets.joborder.index')->with('success', 'Job order approved successfully.');
    }

    public function disapprove(Request $request, int $id): RedirectResponse
    {
        $user = $this->abortUnlessItHead($request);
        $job = $this->jobOrders->find($id);

        if (! $this->jobOrders->disapprove($job, $user)) {
            return $this->notWaitingForApproval($job);
        }

        return redirect()->route('tickets.joborder.index')->with('error', 'Job order disapproved.');
    }

    public function acceptTask(Request $request, int $id): RedirectResponse
    {
        $this->jobOrders->accept($this->jobOrders->find($id), $this->actor($request));

        return back();
    }

    public function markAsDone(Request $request, int $id): RedirectResponse
    {
        $this->jobOrders->complete($this->jobOrders->find($id), $this->actor($request));

        return back();
    }

    public function addNote(AddJobOrderNoteRequest $request, int $id): RedirectResponse
    {
        $this->jobOrders->addNote($this->jobOrders->find($id), $request->validated(), $this->actor($request));

        return back();
    }

    public function addFiles(AddJobOrderFilesRequest $request, int $id): RedirectResponse
    {
        $this->jobOrders->addFiles($this->jobOrders->find($id), $request->file('files', []), $this->actor($request));

        flash('Files uploaded successfully.')->info();

        return back();
    }

    public function downloadFile(int $id, int $file): BinaryFileResponse
    {
        ['path' => $path, 'name' => $name] = $this->jobOrders->attachment($id, $file);

        return response()->download($path, $name);
    }

    public function export(string $type): HttpResponse
    {
        return match ($type) {
            'excel' => Excel::download(new JobOrdersExport($this->jobOrders->excelExportRows()), 'job_orders.xlsx'),
            'pdf' => Pdf::loadView('it_department.export.pdf', ['data' => $this->jobOrders->pdfExportRows()])
                ->setPaper('a4', 'landscape')
                ->download('job_orders.pdf'),
            default => back()->with('error', 'Invalid export type selected.'),
        };
    }

    public function print(Request $request, int $id): Response
    {
        return Inertia::render('it/job-orders/print', [
            'job' => JobOrderPrintResource::make($this->jobOrders->find($id))->resolve($request),
            'appName' => (string) config('app.name', 'Jell Group'),
            'printed' => now()->timezone(config('app.timezone', 'Asia/Manila'))->format('F d, Y h:i A'),
        ]);
    }

    private function actor(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    /** Approve, disapprove and delete are for the IT Head (and Developer) only. */
    private function abortUnlessItHead(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->hasAnyRole(['IT Head', 'Developer']), 403);

        return $user;
    }

    private function notWaitingForApproval(JobOrder $job): RedirectResponse
    {
        return redirect()
            ->route('tickets.joborder.index')
            ->with('warning', "This job order is not waiting for approval. Current status: {$job->job_status} / {$job->approval_status}");
    }
}
