<?php

declare(strict_types=1);

namespace App\Repositories\HR;

use App\Models\Employee;
use App\Models\EmployeeAttachment;
use App\Repositories\Contracts\HR\EmployeeAttachmentRepositoryInterface;

final class EmployeeAttachmentRepository implements EmployeeAttachmentRepositoryInterface
{
    public function findOrFail(Employee $employee, int $attachmentId): EmployeeAttachment
    {
        return $employee->attachments()->findOrFail($attachmentId);
    }

    public function create(Employee $employee, array $attributes): EmployeeAttachment
    {
        return $employee->attachments()->create($attributes);
    }

    public function delete(EmployeeAttachment $attachment): void
    {
        $attachment->delete();
    }
}
