<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Models\Employee;
use App\Models\EmployeeAttachment;
use App\Repositories\Contracts\HR\EmployeeAttachmentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/** Extra files on the 201 profile, kept on the private disk under random names. */
final class EmployeeAttachmentService
{
    private const DISK = 'local';

    public function __construct(
        private readonly EmployeeAttachmentRepositoryInterface $attachments,
        private readonly EmployeeAuditService $audit,
    ) {}

    public function store(Employee $employee, UploadedFile $file): EmployeeAttachment
    {
        $path = $file->storeAs('employees/attachments', bin2hex(random_bytes(24)).'.'.$file->extension(), self::DISK);
        if (! $path || ! Storage::disk(self::DISK)->exists($path)) {
            throw new RuntimeException('Failed to save employee attachment.');
        }

        try {
            $attachment = $this->attachments->create($employee, [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        } catch (Throwable $exception) {
            Storage::disk(self::DISK)->delete($path);

            throw $exception;
        }

        $this->audit->log($employee, 'uploaded_attachment', [
            'file_name' => $file->getClientOriginalName(),
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return $attachment;
    }

    /** @return array{path: string, name: string} absolute path and download name; 404 when the file is gone */
    public function download(Employee $employee, int $attachmentId): array
    {
        $attachment = $this->attachments->findOrFail($employee, $attachmentId);
        abort_unless(Storage::disk(self::DISK)->exists($attachment->file_path), 404);

        return ['path' => Storage::disk(self::DISK)->path($attachment->file_path), 'name' => $attachment->file_name];
    }

    public function delete(Employee $employee, int $attachmentId): void
    {
        $attachment = $this->attachments->findOrFail($employee, $attachmentId);
        Storage::disk(self::DISK)->delete($attachment->file_path);
        $this->audit->log($employee, 'deleted_attachment', [
            'file_name' => $attachment->file_name,
            'file_path' => $attachment->file_path,
        ]);
        $this->attachments->delete($attachment);
    }
}
