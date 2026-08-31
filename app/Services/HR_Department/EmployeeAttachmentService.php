<?php

declare(strict_types=1);

namespace App\Services\HR_Department;

use App\Models\Employee;
use App\Models\EmployeeAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class EmployeeAttachmentService
{
    public function __construct(private readonly EmployeeAuditService $audit) {}

    public function store(Employee $employee, UploadedFile $file): EmployeeAttachment
    {
        $newName = bin2hex(random_bytes(24)).'.'.$file->extension();
        $path = $file->storeAs('employees/attachments', $newName, 'local');

        if (! $path || ! Storage::disk('local')->exists($path)) {
            throw new RuntimeException('Failed to save employee attachment.');
        }

        try {
            $attachment = $employee->attachments()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
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

    public function delete(Employee $employee, int $attachmentId): void
    {
        $attachment = $employee->attachments()->findOrFail($attachmentId);
        Storage::disk('local')->delete($attachment->file_path);
        $this->audit->log($employee, 'deleted_attachment', [
            'file_name' => $attachment->file_name,
            'file_path' => $attachment->file_path,
        ]);
        $attachment->delete();
    }
}
