<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Http\Resources\Concerns\FormatsDates;
use App\Models\Employee;
use App\Models\EmployeeAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

/**
 * The employee 201 profile (`hr/employees/show`): `employee` summary, edit-form `profileValues`,
 * 201 file `assets`, `statusDetails` and `attachments`. Load `asset`, `attachments`, `position`
 * and `department` first.
 *
 * @mixin Employee
 */
final class EmployeeProfileResource extends JsonResource
{
    use FormatsDates;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $asset = $this->asset;
        $age = $this->age();

        return [
            'employee' => [
                'id' => $this->id,
                'name' => $this->full_name,
                'position' => $this->position?->title ?? 'No Position',
                'department' => $this->department?->name ?? 'No Department',
                'status' => $this->status ?? 'Active',
                'employee_id_permanent' => $this->employee_id_permanent,
                'photo_url' => $asset?->profile_picture ? route('employees.staff.profile-picture', $this->resource) : null,
                'qr_svg' => filled($this->employee_id_permanent) ? $this->qr((string) $this->employee_id_permanent) : null,
                'hired' => $this->formatDate($this->date_hired, 'M d, Y') ?? '—',
                'tenure' => $this->tenure(),
                'age' => $age === null ? '—' : "{$age} yrs old",
                'address_1' => $this->address_1,
                'address_2' => $this->address_2,
                'emergency_name' => $this->emergency_name,
                'emergency_contact' => $this->emergency_contact,
                'email' => $this->email,
                'phone_number' => $this->phone_number,
                'company' => $this->company,
                'garage' => $this->garage,
            ],
            'profileValues' => [
                'employee_id_permanent' => (string) ($this->employee_id_permanent ?? ''),
                'full_name' => (string) ($this->full_name ?? ''),
                'date_of_birth' => $this->input($this->date_of_birth),
                'status' => (string) ($this->status ?: 'Active'),
                'date_hired' => $this->input($this->date_hired),
                'company' => (string) ($this->company ?? ''),
                'department_id' => $this->department_id ? (string) $this->department_id : '',
                'position_id' => $this->position_id ? (string) $this->position_id : '',
                'garage' => (string) ($this->garage ?: Employee::GARAGES[0]),
                'email' => (string) ($this->email ?? ''),
                'phone_number' => (string) ($this->phone_number ?? ''),
                'address_1' => (string) ($this->address_1 ?? ''),
                'address_2' => (string) ($this->address_2 ?? ''),
                'emergency_name' => (string) ($this->emergency_name ?? ''),
                'emergency_contact' => (string) ($this->emergency_contact ?? ''),
            ],
            'assets' => [
                'updated' => $asset?->updated_at?->diffForHumans(),
                'numbers' => collect(Employee::GOVERNMENT_IDS)->map(fn (string $label, string $key): array => [
                    'key' => $key,
                    'label' => $label,
                    'value' => $asset?->{$key.'_number'},
                    'date' => $this->formatDate($asset?->{$key.'_updated_at'}, 'M d, Y'),
                    'date_input' => $this->input($asset?->{$key.'_updated_at'}),
                ])->values(),
                'files' => collect(Employee::DOCUMENTS)->map(fn (string $label, string $key): array => [
                    'key' => $key,
                    'label' => $label,
                    'url' => $asset?->{$key} ? route('employees.staff.asset-file', [$this->resource, $key]) : null,
                    'name' => $asset?->{$key} ? basename((string) $asset->{$key}) : null,
                    'date' => $this->formatDate($asset?->{$key.'_updated_at'}, 'M d, Y'),
                ])->values(),
            ],
            'statusDetails' => [
                'date_resigned' => $this->input($this->date_resigned),
                'type_of_status' => (string) ($this->type_of_status ?? ''),
                'last_duty' => $this->input($this->last_duty),
                'clearance_date' => $this->input($this->clearance_date),
                'last_pay_status' => (string) ($this->last_pay_status ?? ''),
                'last_pay_date' => $this->input($this->last_pay_date),
            ],
            'attachments' => $this->attachments->map(fn (EmployeeAttachment $attachment): array => [
                'id' => $attachment->id,
                'name' => $attachment->file_name,
                'meta' => strtoupper((string) $attachment->mime_type).' • '.round(((int) $attachment->size) / 1024, 1).' KB',
                'download_url' => route('employees.staff.attachments.download', [$this->id, $attachment->id]),
                'destroy_url' => route('employees.staff.attachments.destroy', [$this->id, $attachment->id]),
            ])->values(),
        ];
    }

    /** Y-m-d for date inputs, or ''. */
    private function input(mixed $value): string
    {
        return $this->formatDate($value, 'Y-m-d') ?? '';
    }

    private function qr(string $value): ?string
    {
        try {
            return (string) QrCode::size(82)->style('round')->margin(0)->backgroundColor(255, 255, 255)->generate($value);
        } catch (Throwable) {
            return null;
        }
    }
}
