<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Enums\LeaveKind;
use App\Models\LeaveRecord;
use App\Repositories\Contracts\HR\EmployeeRepositoryInterface;
use App\Repositories\Contracts\HR\LeaveRepositoryInterface;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * The leave action buttons: 1st / 2nd / Final Notice (each needs picture proof and the
 * one before it), Cancel and Ready for Duty. Notices escalate the employee to Inactive,
 * then Terminated; Cancel and Ready return them to Active.
 */
final class LeaveNoticeService
{
    private const NOTICES = [
        'first' => ['label' => '1st Notice', 'level' => 1, 'proof' => 'first', 'column' => 'first_notice', 'needs' => null, 'status' => null],
        'second' => ['label' => '2nd Notice', 'level' => 2, 'proof' => 'second', 'column' => 'second_notice', 'needs' => ['first_notice', '1st Notice'], 'status' => 'Inactive'],
        'terminate' => ['label' => 'Final Notice', 'level' => 3, 'proof' => 'final', 'column' => 'final_notice', 'needs' => ['second_notice', '2nd Notice'], 'status' => 'Terminated'],
    ];

    public function __construct(
        private readonly LeaveRepositoryInterface $leaves,
        private readonly EmployeeRepositoryInterface $employees,
    ) {}

    /**
     * @return string the success message
     *
     * @throws DomainException when the action is not allowed now
     */
    public function handle(LeaveKind $kind, LeaveRecord $leave, string $action, ?string $note, ?UploadedFile $proof): string
    {
        return match ($action) {
            'first', 'second', 'terminate' => $this->sendNotice($kind, $leave, $action, $note, $proof),
            'cancel' => $this->close($kind, $leave, ['status' => 'Cancelled', 'last_action_note' => $note], 'Leave cancelled. The '.strtolower($kind->noun()).' returned to Active.'),
            'ready' => $this->close($kind, $leave, [
                'status' => 'Completed',
                'offense_level' => 0,
                'ready_for_duty_notified_at' => now('Asia/Manila'),
                'last_action_note' => $note,
            ], "{$kind->noun()} marked as Ready for Duty and returned to Active."),
            default => throw new DomainException('The selected leave action is invalid.'),
        };
    }

    private function sendNotice(LeaveKind $kind, LeaveRecord $leave, string $action, ?string $note, ?UploadedFile $proof): string
    {
        $notice = self::NOTICES[$action];
        if (! $proof) {
            throw new DomainException("Picture proof is required for the {$notice['label']}.");
        }

        $proofPath = $proof->store($kind->proofDirectory($leave->id, $notice['proof']), 'local');

        try {
            DB::transaction(function () use ($kind, $leave, $note, $notice, $proofPath): void {
                $record = $this->lockOpen($kind, $leave);

                if ($notice['needs'] !== null && ! $record->{$notice['needs'][0].'_sent_at'}) {
                    throw new DomainException("Send the {$notice['needs'][1]} before the {$notice['label']}.");
                }
                if ($record->{$notice['column'].'_sent_at'}) {
                    throw new DomainException("The {$notice['label']} has already been sent.");
                }

                $attributes = [
                    $notice['column'].'_sent_at' => now('Asia/Manila'),
                    $notice['column'].'_proof' => $proofPath,
                    'offense_level' => $notice['level'],
                    'last_action_note' => $note,
                ];
                if ($notice['status'] !== null) {
                    $attributes['status'] = $notice['status'];
                }
                $this->leaves->update($record, $attributes);

                if ($notice['status'] !== null && $record->employee !== null) {
                    $this->employees->update($record->employee, ['status' => $notice['status']]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($proofPath);

            throw $exception;
        }

        return match ($action) {
            'first' => '1st Notice marked as sent with picture proof.',
            'second' => '2nd Notice marked as sent. The '.strtolower($kind->noun()).' is now Inactive.',
            default => 'Final Notice marked as sent. The '.strtolower($kind->noun()).' is now Terminated.',
        };
    }

    /** @param array<string, mixed> $attributes */
    private function close(LeaveKind $kind, LeaveRecord $leave, array $attributes, string $message): string
    {
        DB::transaction(function () use ($kind, $leave, $attributes): void {
            $record = $this->lockOpen($kind, $leave);
            $this->leaves->update($record, $attributes);

            if ($record->employee !== null) {
                $this->employees->update($record->employee, ['status' => 'Active']);
            }
        });

        return $message;
    }

    private function lockOpen(LeaveKind $kind, LeaveRecord $leave): LeaveRecord
    {
        $record = $this->leaves->findForUpdate($kind, $leave->id);
        if ($record->isClosed()) {
            throw new DomainException('This leave record is already closed.');
        }

        return $record;
    }
}
