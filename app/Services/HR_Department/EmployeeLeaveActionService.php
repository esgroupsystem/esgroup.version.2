<?php

namespace App\Services\HR_Department;

use App\Models\EmployeeLeave;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class EmployeeLeaveActionService
{
    public function handle(
        EmployeeLeave $leave,
        string $actionType,
        ?string $note,
        ?UploadedFile $proofImage
    ): string {
        return match ($actionType) {
            'first' => $this->markFirstNotice($leave, $note, $proofImage),
            'second' => $this->markSecondNotice($leave, $note, $proofImage),
            'terminate' => $this->markFinalNotice($leave, $note, $proofImage),
            'cancel' => $this->cancelLeave($leave, $note),
            'ready' => $this->markReadyForDuty($leave, $note),
            default => throw new DomainException('The selected leave action is invalid.'),
        };
    }

    private function markFirstNotice(
        EmployeeLeave $leave,
        ?string $note,
        ?UploadedFile $proofImage
    ): string {
        if (! $proofImage) {
            throw new DomainException('Picture proof is required for the 1st Notice.');
        }

        $proofPath = $this->storeProof($proofImage, $leave->id, 'first');

        try {
            DB::transaction(function () use ($leave, $note, $proofPath): void {
                $record = $this->lockLeave($leave->id);

                $this->assertOpen($record);

                if ($record->first_notice_sent_at) {
                    throw new DomainException('The 1st Notice has already been sent.');
                }

                $record->update([
                    'first_notice_sent_at' => now('Asia/Manila'),
                    'first_notice_proof' => $proofPath,
                    'offense_level' => 1,
                    'last_action_note' => $note,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($proofPath);

            throw $exception;
        }

        return '1st Notice marked as sent with picture proof.';
    }

    private function markSecondNotice(
        EmployeeLeave $leave,
        ?string $note,
        ?UploadedFile $proofImage
    ): string {
        if (! $proofImage) {
            throw new DomainException('Picture proof is required for the 2nd Notice.');
        }

        $proofPath = $this->storeProof($proofImage, $leave->id, 'second');

        try {
            DB::transaction(function () use ($leave, $note, $proofPath): void {
                $record = $this->lockLeave($leave->id);

                $this->assertOpen($record);

                if (! $record->first_notice_sent_at) {
                    throw new DomainException('Send the 1st Notice before the 2nd Notice.');
                }

                if ($record->second_notice_sent_at) {
                    throw new DomainException('The 2nd Notice has already been sent.');
                }

                $record->update([
                    'second_notice_sent_at' => now('Asia/Manila'),
                    'second_notice_proof' => $proofPath,
                    'offense_level' => 2,
                    'status' => 'Inactive',
                    'last_action_note' => $note,
                ]);

                $record->employee?->update([
                    'status' => 'Inactive',
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($proofPath);

            throw $exception;
        }

        return '2nd Notice marked as sent. The employee is now Inactive.';
    }

    private function markFinalNotice(
        EmployeeLeave $leave,
        ?string $note,
        ?UploadedFile $proofImage
    ): string {
        if (! $proofImage) {
            throw new DomainException('Picture proof is required for the Final Notice.');
        }

        $proofPath = $this->storeProof($proofImage, $leave->id, 'final');

        try {
            DB::transaction(function () use ($leave, $note, $proofPath): void {
                $record = $this->lockLeave($leave->id);

                $this->assertOpen($record);

                if (! $record->second_notice_sent_at) {
                    throw new DomainException('Send the 2nd Notice before the Final Notice.');
                }

                if ($record->final_notice_sent_at) {
                    throw new DomainException('The Final Notice has already been sent.');
                }

                $record->update([
                    'final_notice_sent_at' => now('Asia/Manila'),
                    'final_notice_proof' => $proofPath,
                    'offense_level' => 3,
                    'status' => 'Terminated',
                    'last_action_note' => $note,
                ]);

                $record->employee?->update([
                    'status' => 'Terminated',
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($proofPath);

            throw $exception;
        }

        return 'Final Notice marked as sent. The employee is now Terminated.';
    }

    private function cancelLeave(EmployeeLeave $leave, ?string $note): string
    {
        DB::transaction(function () use ($leave, $note): void {
            $record = $this->lockLeave($leave->id);

            $this->assertOpen($record);

            $record->update([
                'status' => 'Cancelled',
                'last_action_note' => $note,
            ]);

            $record->employee?->update([
                'status' => 'Active',
            ]);
        });

        return 'Leave cancelled. The employee returned to Active.';
    }

    private function markReadyForDuty(EmployeeLeave $leave, ?string $note): string
    {
        DB::transaction(function () use ($leave, $note): void {
            $record = $this->lockLeave($leave->id);

            $this->assertOpen($record);

            $record->update([
                'status' => 'Completed',
                'offense_level' => 0,
                'ready_for_duty_notified_at' => now('Asia/Manila'),
                'last_action_note' => $note,
            ]);

            $record->employee?->update([
                'status' => 'Active',
            ]);
        });

        return 'Employee marked as Ready for Duty and returned to Active.';
    }

    private function lockLeave(int $leaveId): EmployeeLeave
    {
        return EmployeeLeave::query()
            ->with('employee')
            ->lockForUpdate()
            ->findOrFail($leaveId);
    }

    private function assertOpen(EmployeeLeave $leave): void
    {
        if ($leave->isClosed()) {
            throw new DomainException('This leave record is already closed.');
        }
    }

    private function storeProof(
        UploadedFile $proofImage,
        int $leaveId,
        string $noticeType
    ): string {
        return $proofImage->store(
            "employee-leave/notices/{$leaveId}/{$noticeType}",
            'public'
        );
    }
}
