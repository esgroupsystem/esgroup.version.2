<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\HR;

use App\Models\Employee;
use App\Models\EmployeeAttachment;

interface EmployeeAttachmentRepositoryInterface
{
    public function findOrFail(Employee $employee, int $attachmentId): EmployeeAttachment;

    /** @param array<string, mixed> $attributes */
    public function create(Employee $employee, array $attributes): EmployeeAttachment;

    public function delete(EmployeeAttachment $attachment): void;
}
