<?php

declare(strict_types=1);

namespace App\Services\HR_Department;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class EmployeeService
{
    public function __construct(private readonly EmployeeAuditService $audit) {}

    /** @param  array<string, mixed>  $data */
    public function create(array $data): Employee
    {
        $employee = DB::transaction(function () use ($data): Employee {
            $lastId = Employee::query()->lockForUpdate()->max('id') ?? 0;
            $employeeId = 'EMP-'.str_pad((string) ($lastId + 1), 4, '0', STR_PAD_LEFT);

            return Employee::query()->create([
                'employee_id' => $employeeId,
                'employee_id_permanent' => $data['employee_id_permanent'] ?? null,
                'full_name' => $data['full_name'],
                'department_id' => $data['department_id'] ?? null,
                'position_id' => $data['position_id'] ?? null,
                'email' => $data['email'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'company' => $data['company'],
                'garage' => $data['garage'],
            ]);
        });

        $this->audit->log($employee, 'created', [
            'employee_id' => $employee->employee_id,
            'full_name' => $employee->full_name,
        ]);

        return $employee;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(
        Employee $employee,
        array $data,
        bool $removeProfilePicture,
        ?string $croppedImage,
        ?UploadedFile $profilePicture,
    ): Employee {
        $employeeFields = [
            'employee_id_permanent', 'full_name', 'status', 'date_hired', 'company',
            'department_id', 'position_id', 'email', 'phone_number', 'garage',
            'date_of_birth', 'address_1', 'address_2', 'emergency_name', 'emergency_contact',
        ];

        $employeeData = array_intersect_key($data, array_flip($employeeFields));
        $before = $employee->only(array_keys($employeeData));

        foreach (['date_hired', 'date_of_birth'] as $dateField) {
            if (array_key_exists($dateField, $employeeData)) {
                $employeeData[$dateField] = $this->normalizeDate($employeeData[$dateField]);
            }
        }

        $employee->update($employeeData);
        $changed = $this->diffChanges($before, $employee->fresh()->only(array_keys($employeeData)));
        $asset = $employee->asset ?? $employee->asset()->create([]);

        if ($removeProfilePicture) {
            if ($asset->profile_picture) {
                Storage::disk('local')->delete($asset->profile_picture);
            }
            $asset->forceFill(['profile_picture' => null])->save();
            $changed['profile_picture'] = ['from' => 'existing', 'to' => null];
        }

        if (filled($croppedImage)) {
            if (strlen($croppedImage) > 4 * 1024 * 1024) {
                throw ValidationException::withMessages(['profile_picture_cropped' => 'The cropped image is too large.']);
            }

            if (preg_match('/^data:image\/\w+;base64,/', $croppedImage) === 1) {
                $binary = base64_decode(substr($croppedImage, strpos($croppedImage, ',') + 1), true);
                if ($binary === false || strlen($binary) > 2 * 1024 * 1024 || @getimagesizefromstring($binary) === false) {
                    throw ValidationException::withMessages(['profile_picture_cropped' => 'The cropped image is invalid or too large.']);
                }

                if ($asset->profile_picture) {
                    Storage::disk('local')->delete($asset->profile_picture);
                }

                $cleanName = strtolower((string) preg_replace('/[^a-z0-9_-]+/i', '_', $employee->full_name));
                $permanentId = $employee->employee_id_permanent ?? $employee->id;
                $fileName = "employees/{$cleanName}_{$permanentId}.jpg";
                Storage::disk('local')->put($fileName, $binary);
                $asset->forceFill(['profile_picture' => $fileName])->save();
                $changed['profile_picture'] = ['from' => 'existing', 'to' => $fileName];
            }
        } elseif ($profilePicture !== null) {
            if ($asset->profile_picture) {
                Storage::disk('local')->delete($asset->profile_picture);
            }

            $name = 'profile_'.$employee->id.'_'.time().'.'.$profilePicture->getClientOriginalExtension();
            $path = $profilePicture->storeAs('employees', $name, 'local');
            $asset->forceFill(['profile_picture' => $path])->save();
            $changed['profile_picture'] = ['from' => 'existing', 'to' => $path];
        }

        $this->audit->log($employee, 'updated_profile', ['changed' => $changed]);

        return $employee->fresh(['asset']);
    }

    /** @param  array<string, mixed>  $data */
    public function updateStatusDetails(Employee $employee, array $data): Employee
    {
        $before = $employee->only(array_keys($data));
        foreach (['date_resigned', 'last_duty', 'clearance_date', 'last_pay_date'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->normalizeDate($data[$field]);
            }
        }

        $employee->update($data);
        $this->audit->log($employee, 'updated_status_details', [
            'changed' => $this->diffChanges($before, $employee->fresh()->only(array_keys($data))),
        ]);

        return $employee->fresh();
    }

    /** @param  array<string, mixed>  $data */
    public function updateAssets(Employee $employee, array $data): void
    {
        DB::transaction(function () use ($employee, $data): void {
            $asset = $employee->asset ?? $employee->asset()->create([]);
            $before = $asset->only([
                'sss_number', 'tin_number', 'philhealth_number', 'pagibig_number',
                'sss_updated_at', 'tin_updated_at', 'philhealth_updated_at', 'pagibig_updated_at',
                'profile_picture', 'birth_certificate', 'resume', 'contract',
            ]);

            $fields = [
                'sss_number' => 'sss_updated_at',
                'tin_number' => 'tin_updated_at',
                'philhealth_number' => 'philhealth_updated_at',
                'pagibig_number' => 'pagibig_updated_at',
            ];

            foreach ($fields as $numberField => $dateField) {
                $newNumber = ($data[$numberField] ?? null) === '' ? null : ($data[$numberField] ?? null);
                $manualDate = filled($data[$dateField] ?? null) ? Carbon::parse($data[$dateField])->startOfDay() : null;
                if ($asset->{$numberField} != $newNumber) {
                    $asset->{$numberField} = $newNumber;
                    $asset->{$dateField} = $manualDate ?? now();
                } elseif ($manualDate !== null) {
                    $asset->{$dateField} = $manualDate;
                }
            }

            $asset->save();
            $changed = $this->diffChanges($before, $asset->fresh()->only(array_keys($before)));
            if ($changed !== []) {
                $this->audit->log($employee, 'updated_201_file', ['changed' => $changed]);
            }
        });
    }

    public function delete(Employee $employee): void
    {
        DB::transaction(function () use ($employee): void {
            $this->audit->log($employee, 'deleted', [
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->full_name,
            ]);
            $employee->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array<string, array{from: mixed, to: mixed}>
     */
    private function diffChanges(array $before, array $after): array
    {
        $changed = [];
        foreach ($after as $key => $value) {
            $old = ($before[$key] ?? null) === '' ? null : ($before[$key] ?? null);
            $new = $value === '' ? null : $value;
            if ($old != $new) {
                $changed[$key] = ['from' => $old, 'to' => $new];
            }
        }

        return $changed;
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse((string) $value)->format('Y-m-d');
    }
}
