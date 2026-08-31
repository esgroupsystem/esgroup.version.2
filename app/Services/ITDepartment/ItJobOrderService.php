<?php

declare(strict_types=1);

namespace App\Services\ITDepartment;

use App\Enums\ItTicketApprovalStatus;
use App\Enums\ItTicketStatus;
use App\Events\JobOrderCreated;
use App\Helpers\Notifier;
use App\Mail\JobOrderCreatedMail;
use App\Models\BusDetail;
use App\Models\JobOrder;
use App\Models\JobOrderFile;
use App\Models\JobOrderLog;
use App\Models\JobOrderNote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

final class ItJobOrderService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function create(array $data, array $files, User $actor): JobOrder
    {
        $job = DB::transaction(function () use ($data, $files, $actor): JobOrder {
            $bus = BusDetail::query()->whereKey($data['bus_detail_id'])->lockForUpdate()->first();
            if ($bus === null) {
                throw new RuntimeException('The selected bus no longer exists.');
            }

            $job = JobOrder::query()->create([
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
            $this->log($job, $actor, 'created', [
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

    public function approve(JobOrder $job, User $actor): bool
    {
        if ($job->approval_status !== ItTicketApprovalStatus::Approval->value) {
            return false;
        }

        DB::transaction(function () use ($job, $actor): void {
            $locked = JobOrder::query()->lockForUpdate()->findOrFail($job->id);
            if ($locked->approval_status !== ItTicketApprovalStatus::Approval->value) {
                throw new RuntimeException('This job order is no longer waiting for approval.');
            }
            $locked->update([
                'approval_status' => ItTicketApprovalStatus::Approved->value,
                'job_status' => ItTicketStatus::Pending->value,
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);
            $this->log($locked, $actor, 'approved', [
                'message' => 'Approved by IT Head',
                'job_status' => ItTicketStatus::Pending->value,
                'approval_status' => ItTicketApprovalStatus::Approved->value,
            ]);
        }, 3);

        return true;
    }

    public function disapprove(JobOrder $job, User $actor): bool
    {
        if ($job->approval_status !== ItTicketApprovalStatus::Approval->value) {
            return false;
        }

        DB::transaction(function () use ($job, $actor): void {
            $locked = JobOrder::query()->lockForUpdate()->findOrFail($job->id);
            $locked->update([
                'approval_status' => ItTicketApprovalStatus::Disapproved->value,
                'job_status' => ItTicketStatus::Disapproved->value,
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);
            $this->log($locked, $actor, 'disapproved', [
                'message' => 'Disapproved by IT Head',
                'job_status' => ItTicketStatus::Disapproved->value,
                'approval_status' => ItTicketApprovalStatus::Disapproved->value,
            ]);
        }, 3);

        return true;
    }

    public function delete(JobOrder $job): bool
    {
        $status = ItTicketStatus::tryFrom((string) $job->job_status);
        if ($status !== null && ! $status->canBeDeleted()) {
            return false;
        }

        DB::transaction(function () use ($job): void {
            $locked = JobOrder::query()->with('files')->lockForUpdate()->findOrFail($job->id);
            foreach ($locked->files as $file) {
                if ($file->file_path) {
                    Storage::disk('local')->delete($file->file_path);
                }
            }
            Storage::disk('local')->deleteDirectory("joborders/{$locked->id}");
            JobOrderFile::query()->where('job_id', $locked->id)->delete();
            JobOrderNote::query()->where('joborder_id', $locked->id)->delete();
            JobOrderLog::query()->where('joborder_id', $locked->id)->delete();
            $locked->delete();
        }, 3);

        return true;
    }

    /** @param array<string, mixed> $data */
    public function addNote(JobOrder $job, array $data, User $actor): void
    {
        DB::transaction(function () use ($job, $data, $actor): void {
            JobOrderNote::query()->create([
                'joborder_id' => $job->id,
                'user_id' => $actor->id,
                'reason' => $data['reason'],
                'details' => $data['details'] ?? null,
            ]);
            $this->log($job, $actor, 'added note', ['reason' => $data['reason']]);
        });
    }

    /** @param array<int, UploadedFile> $files */
    public function addFiles(JobOrder $job, array $files, User $actor): void
    {
        DB::transaction(function () use ($job, $files, $actor): void {
            $stored = $this->storeFiles($job, $files);
            if ($stored > 0) {
                $this->log($job, $actor, 'added file', ['file_count' => $stored]);
            }
        });
    }

    public function accept(JobOrder $job, User $actor): bool
    {
        if ($job->job_status !== ItTicketStatus::Pending->value) {
            return false;
        }
        $job->update([
            'job_assign_person' => $actor->full_name,
            'job_status' => ItTicketStatus::InProgress->value,
        ]);
        $this->log($job, $actor, 'accepted task', ['message' => 'Task accepted by IT officer']);

        return true;
    }

    public function complete(JobOrder $job, User $actor): bool
    {
        if ($job->job_status !== ItTicketStatus::InProgress->value) {
            return false;
        }
        $job->update(['job_status' => ItTicketStatus::Completed->value]);
        $this->log($job, $actor, 'completed', ['message' => 'Task marked as done']);

        return true;
    }

    /** @param array<string, mixed> $data */
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
        $job->update($payload);

        $changes = [];
        foreach ($original as $field => $oldValue) {
            $newValue = $job->getAttribute($field);
            if ($oldValue != $newValue) {
                $changes[$field] = ['old' => $oldValue ?? 'None', 'new' => $newValue ?? 'None'];
            }
        }
        if ($changes !== []) {
            $this->log($job, $actor, 'updated details', $changes);
        }
    }

    /** @param array<int, UploadedFile> $files */
    private function storeFiles(JobOrder $job, array $files): int
    {
        $storedCount = 0;
        foreach ($files as $upload) {
            if (! $upload->isValid()) {
                continue;
            }
            $storedPath = $upload->store("joborders/{$job->id}", 'local');
            if (! $storedPath || ! Storage::disk('local')->exists($storedPath)) {
                throw new RuntimeException("Failed to save attachment: {$upload->getClientOriginalName()}");
            }
            JobOrderFile::query()->create([
                'job_id' => $job->id,
                'file_name' => $upload->getClientOriginalName(),
                'file_remarks' => null,
                'file_notes' => null,
                'file_path' => $storedPath,
            ]);
            $storedCount++;
        }

        return $storedCount;
    }

    /** @param array<string, mixed> $meta */
    private function log(JobOrder $job, User $actor, string $action, array $meta): void
    {
        JobOrderLog::query()->create([
            'joborder_id' => $job->id,
            'user_id' => $actor->id,
            'action' => $action,
            'meta' => $meta,
        ]);
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
