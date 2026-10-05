<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

/** Reject an OT / offset / salary adjustment, with an optional reason. */
final class RejectAttendanceAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['rejection_reason' => ['nullable', 'string', 'max:2000']];
    }
}
