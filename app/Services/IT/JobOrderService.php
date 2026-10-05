<?php

declare(strict_types=1);

namespace App\Services\IT;

use App\Enums\ItTicketApprovalStatus;
use App\Enums\ItTicketStatus;
use App\Events\JobOrderCreated;
use App\Helpers\Notifier;
use App\Mail\JobOrderCreatedMail;
use App\Models\BusDetail;
use App\Models\JobOrder;
use App\Models\JobOrderLog;
use App\Models\User;
use App\Repositories\Contracts\Fleet\BusDetailRepositoryInterface;
use App\Repositories\Contracts\IT\JobOrderRepositoryInterface;
use App\Repositories\Contracts\Security\UserRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Tickets Job Order: list tabs, counts, the approval workflow and files/notes/logs.
 */
final class JobOrderService
{
    public const TABS = ['pending', 'progress', 'completed'];

    private const DISK = 'local';

    public function __construct(
        private readonly JobOrderRepositoryInterface $jobOrders,
        private readonly BusDetailRepositoryInterface $buses,
        private readonly UserRepositoryInterface $users,
    ) {}

    /** @return LengthAwarePaginator<int, JobOrder> */
    public function paginateTab(string $tab, string $search): LengthAwarePaginator
    {
        return $this->jobOrders->paginateByStatus($this->tabStatuses($tab), $search);
    }

    /** @return array{new: int, pending: int, progress: int, completed: int} */
    public function stats(): array
    {
        return [
            'new' => $this->jobOrders->countCreatedOn(today()),
            'pending' => $this->jobOrders->countByStatus($this->tabStatuses('pending')),
            'progress' => $this->jobOrders->countByStatus($this->tabStatuses('progress')),
            'completed' => $this->jobOrders->countByStatus($this->tabStatuses('completed')),
        ];
    }

    /** @return list<array{name: string, total: int}> every category, in the fixed order */
    public function categorySummary(): array
    {
        $counts = $this->jobOrders->countByType();

        return array_map(
            fn (string $category): array => ['name' => $category, 'total' => (int) ($counts[$category] ?? 0)],
            JobOrder::CATEGORIES,
        );
    }

    /** @return Collection<int, User> IT staff with their assigned job order count */
    public function agents(): Collection
    {
        return $this->users->withRolesAndAssignedJobOrders(['IT Head', 'IT Officer', 'IT Technician']);
    }

    /** @return Collection<int, JobOrder> */
    public function createdSince(CarbonInterface $from): Collection
    {
        return $this->jobOrders->createdSince($from);
    }

    /** @return Collection<int, JobOrder> newest open job orders (approval, pending, in progress) */
    public function unresolved(int $limit): Collection
    {
        return $this->jobOrders->latestByStatus(
            [ItTicketStatus::Pending->value, ItTicketStatus::Approval->value, ItTicketStatus::InProgress->value],
            $limit,
        );
    }

    /** @return Collection<int, BusDetail> */
    public function busOptions(): Collection
    {
        return $this->buses->options();
    }

    public function find(int $id): JobOrder
    {
        return $this->jobOrders->findOrFail($id, ['bus']);
    }

    /**
     * Loads the job order for its detail page and records the view.
     *
     * @return array{job: JobOrder, logs: Collection<int, JobOrderLog>}
     */
    public function openForViewer(int $id, User $viewer): array
    {
        $job = $this->jobOrders->findOrFail($id, ['bus', 'files', 'notes.user']);
        $this->jobOrders->recordView($job, $viewer->id);

        return ['job' => $job, 'logs' => $this->jobOrders->logs($job)];
    }

    /** @return array{path: string, name: string} absolute path and download name */
    public function attachment(int $jobId, int $fileId): array
    {
        $file = $this->jobOrders->findFileOrFail($this->jobOrders->findOrFail($jobId), $fileId);
        abort_unless(Storage::disk(self::DISK)->exists($file->file_path), 404);

        return [
            'path' => Storage::disk(self::DISK)->path($file->file_path),
            'name' => $file->file_name ?: basename($file->file_path),
        ];
    }

    /** @return Collection<int, JobOrder> */
    public function pdfExportRows(): Collection
    {
        return $this->jobOrders->latestWithBus(5000);
    }

    /** @return Collection<int, JobOrder> */
    public function excelExportRows(): Collection
    {
        return $this->jobOrders->exportRows();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function create(array $data, array $files, User $actor): JobOrder
    {
        $job = DB::transaction(function () use ($data, $files, $actor): JobOrder {
            $bus = $this->buses->lockForUpdate((int) $data['bus_detail_id']);
            if ($bus === null) {
                throw new RuntimeException('The selected bus no longer exists.');
            }

            $job = $this->jobOrders->create([
                'bus_detail_id' => $bus->id,
                'created_by' => $actor->id,
                'job_name' => $data['job_name'] ?? 'Job Order',
                'job_type' => $data['job_type'],
                'job_datestart' => Carbon::createFromFormat('d/m/y', (string) $data['job_datestart'])->format('Y-m-d'),
                'job_time_start' => $data['job_time_start'],
                'job_time_end' => $data['job_time_end'],
                'job_sitNumber' => $data['job_sitNumber'] ?? null,
                'job_remarks' => $data['job_remarks'] ?? null,
                'approval_status' => ItTicketApprovalStatus::Approval->value,
                'job_status' => ItTicketStatus::Approval->value,
                'job_assign_person' => null,
                'job_date_filled' => now(),
                'job_creator' => $actor->full_name ?? $actor->name ?? $actor->username ?? 'System',
                'driver_name' => $data['driver_name'] ?? null,
                'conductor_name' => $data['conductor_name'] ?? null,
                'direction' => $data['direction'] ?? null,
            ]);

            $this->storeFiles($job, $files);
            $this->jobOrders->addLog($job, $actor->id, 'created', [
                'job_type' => $job->job_type,
                'status' => $job->job_status,
                'bus_detail_id' => $bus->id,
                'body_number' => $bus->body_number,
                'plate_number' => $bus->plate_number,
            ]);

            return $job->load('bus');
        }, 3);

        $this->dispatchCreatedReactions($job);

        return $job;
    }

    /** False when the job order is not waiting for approval. */
    public function approve(JobOrder $job, User $actor): bool
    {
        if ($job->approval_status !== ItTicketApprovalStatus::Approval->value) {
            return false;
        }

        DB::transaction(function () use ($job, $actor): void {
            $locked = $this->jobOrders->findForUpdate($job->id);
            if ($locked->approval_status !== ItTicketApprovalStatus::Approval->value) {
                throw new RuntimeException('This job order is no longer waiting for approval.');
            }
            $this->jobOrders->update($locked, [
                'approval_status' => ItTicketApprovalStatus::Approved->value,
                'job_status' => ItTicketStatus::Pending->value,
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);
            $this->jobOrders->addLog($locked, $actor->id, 'approved', [
                'message' => 'Approved by IT Head',
                'job_status' => ItTicketStatus::Pending->value,
                'approval_status' => ItTicketApprovalStatus::Approved->value,
            ]);
        }, 3);

        return true;
    }

    /** False when the job order is not waiting for approval. */
    public function disapprove(JobOrder $job, User $actor): bool
    {
        if ($job->approval_status !== ItTicketApprovalStatus::Approval->value) {
            return false;
        }

        DB::transaction(function () use ($job, $actor): void {
            $locked = $this->jobOrders->findForUpdate($job->id);
            $this->jobOrders->update($locked, [
                'approval_status' => ItTicketApprovalStatus::Disapproved->value,
                'job_status' => ItTicketStatus::Disapproved->value,
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);
            $this->jobOrders->addLog($locked, $actor->id, 'disapproved', [
                'message' => 'Disapproved by IT Head',
                'job_status' => ItTicketStatus::Disapproved->value,
                'approval_status' => ItTicketApprovalStatus::Disapproved->value,
            ]);
        }, 3);

        return true;
    }

    /** False when the job order is In Progress or Completed. */
    public function delete(JobOrder $job): bool
    {
        $status = ItTicketStatus::tryFrom((string) $job->job_status);
        if ($status !== null && ! $status->canBeDeleted()) {
            return false;
        }

        DB::transaction(function () use ($job): void {
            $locked = $this->jobOrders->findForUpdate($job->id, ['files']);
            foreach ($locked->files as $file) {
                if ($file->file_path) {
                    Storage::disk(self::DISK)->delete($file->file_path);
                }
            }
            Storage::disk(self::DISK)->deleteDirectory("joborders/{$locked->id}");
            $this->jobOrders->delete($locked);
        }, 3);

        return true;
    }

    /** @param array<string, mixed> $data */
    public function addNote(JobOrder $job, array $data, User $actor): void
    {
        DB::transaction(function () use ($job, $data, $actor): void {
            $this->jobOrders->addNote($job, [
                'user_id' => $actor->id,
                'reason' => $data['reason'],
                'details' => $data['details'] ?? null,
            ]);
            $this->jobOrders->addLog($job, $actor->id, 'added note', ['reason' => $data['reason']]);
        });
    }

    /** @param array<int, UploadedFile> $files */
    public function addFiles(JobOrder $job, array $files, User $actor): void
    {
        DB::transaction(function () use ($job, $files, $actor): void {
            $stored = $this->storeFiles($job, $files);
            if ($stored > 0) {
                $this->jobOrders->addLog($job, $actor->id, 'added file', ['file_count' => $stored]);
            }
        });
    }

    /** Pending → In Progress, assigned to the actor. */
    public function accept(JobOrder $job, User $actor): bool
    {
        if ($job->job_status !== ItTicketStatus::Pending->value) {
            return false;
        }
        $this->jobOrders->update($job, [
            'job_assign_person' => $actor->full_name,
            'job_status' => ItTicketStatus::InProgress->value,
        ]);
        $this->jobOrders->addLog($job, $actor->id, 'accepted task', ['message' => 'Task accepted by IT officer']);

        return true;
    }

    /** In Progress → Completed. */
    public function complete(JobOrder $job, User $actor): bool
    {
        if ($job->job_status !== ItTicketStatus::InProgress->value) {
            return false;
        }
        $this->jobOrders->update($job, ['job_status' => ItTicketStatus::Completed->value]);
        $this->jobOrders->addLog($job, $actor->id, 'completed', ['message' => 'Task marked as done']);

        return true;
    }

    /**
     * Saves the edited details and logs each changed field as old → new.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(JobOrder $job, array $data, User $actor): void
    {
        if (isset($data['job_datestart'])) {
            $date = (string) $data['job_datestart'];
            $data['job_datestart'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
                ? Carbon::parse($date)->format('Y-m-d')
                : Carbon::createFromFormat('d/m/y', $date)->format('Y-m-d');
        }

        $fields = ['job_type', 'job_datestart', 'job_time_start', 'job_time_end', 'direction', 'job_sitNumber', 'job_remarks', 'driver_name', 'conductor_name'];
        $payload = array_intersect_key($data, array_flip($fields));
        $original = $job->only(array_keys($payload));
        $this->jobOrders->update($job, $payload);

        $changes = [];
        foreach ($original as $field => $oldValue) {
            $newValue = $job->getAttribute($field);
            if ($oldValue != $newValue) {
                $changes[$field] = ['old' => $oldValue ?? 'None', 'new' => $newValue ?? 'None'];
            }
        }
        if ($changes !== []) {
            $this->jobOrders->addLog($job, $actor->id, 'updated details', $changes);
        }
    }

    /** @return list<string> */
    private function tabStatuses(string $tab): array
    {
        return match ($tab) {
            'progress' => [ItTicketStatus::InProgress->value],
            'completed' => [ItTicketStatus::Completed->value],
            default => [ItTicketStatus::Pending->value, ItTicketStatus::Approval->value],
        };
    }

    /** @param array<int, UploadedFile> $files */
    private function storeFiles(JobOrder $job, array $files): int
    {
        $storedCount = 0;
        foreach ($files as $upload) {
            if (! $upload->isValid()) {
                continue;
            }
            $storedPath = $upload->store("joborders/{$job->id}", self::DISK);
            if (! $storedPath || ! Storage::disk(self::DISK)->exists($storedPath)) {
                throw new RuntimeException("Failed to save attachment: {$upload->getClientOriginalName()}");
            }
            $this->jobOrders->addFile($job, [
                'file_name' => $upload->getClientOriginalName(),
                'file_remarks' => null,
                'file_notes' => null,
                'file_path' => $storedPath,
            ]);
            $storedCount++;
        }

        return $storedCount;
    }

    private function dispatchCreatedReactions(JobOrder $job): void
    {
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
            Notifier::notifyRoles(['IT Head', 'IT Officer'], new JobOrderCreatedMail($job));
        } catch (Throwable $exception) {
            Log::error('Job-order email queueing failed.', [
                'job_order_id' => $job->id,
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
            ]);
        }
    }
}
