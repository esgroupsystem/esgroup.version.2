<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Enums\EmployeeStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Repositories\Contracts\HR\DepartmentRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Employee List and the 201 profile: directory, numbering, profile / status / 201 file
 * updates (with the audit trail), the biometric link and the private files.
 */
final class EmployeeService
{
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    private const DISK = 'local';

    private const PROFILE_FIELDS = [
        'employee_id_permanent', 'full_name', 'status', 'date_hired', 'company',
        'department_id', 'position_id', 'email', 'phone_number', 'garage',
        'date_of_birth', 'address_1', 'address_2', 'emergency_name', 'emergency_contact',
    ];

    private const ASSET_AUDIT_FIELDS = [
        'sss_number', 'tin_number', 'philhealth_number', 'pagibig_number',
        'sss_updated_at', 'tin_updated_at', 'philhealth_updated_at', 'pagibig_updated_at',
        'profile_picture', 'birth_certificate', 'resume', 'contract',
    ];

    public function __construct(
        private readonly EmployeeRepositoryInterface $employees,
        private readonly DepartmentRepositoryInterface $departments,
        private readonly EmployeeAuditService $audit,
    ) {}

    public function perPage(mixed $value): int
    {
        $perPage = (int) $value;

        return in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : self::PER_PAGE_OPTIONS[0];
    }

    /**
     * @param  array<string, mixed>  $filters  the request query (search, status, company, garage, per_page)
     * @return LengthAwarePaginator<int, Employee>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $exact = array_filter(
            array_intersect_key($filters, array_flip(['status', 'company', 'garage'])),
            fn (mixed $value): bool => filled($value),
        );

        return $this->employees->paginateDirectory(
            trim((string) ($filters['search'] ?? '')),
            array_map('strval', $exact),
            $this->perPage($filters['per_page'] ?? null),
            $filters,
        );
    }

    /** @return array{total: int, active: int, inactive: int, suspended: int, companies: int, garages: int} */
    public function stats(): array
    {
        return [
            'total' => $this->employees->count(),
            'active' => $this->employees->countWithStatus(EmployeeStatus::activeValues()),
            'inactive' => $this->employees->countWithStatus(EmployeeStatus::inactiveValues()),
            'suspended' => $this->employees->countWithStatus([EmployeeStatus::Suspended->value]),
            'companies' => $this->employees->countDistinct('company'),
            'garages' => $this->employees->countDistinct('garage'),
        ];
    }

    /** @return Collection<int, string> companies in use */
    public function companiesInUse(): Collection
    {
        return $this->employees->distinctValues('company');
    }

    /** @return Collection<int, string> garages in use */
    public function garagesInUse(): Collection
    {
        return $this->employees->distinctValues('garage');
    }

    /** @return Collection<int, Department> */
    public function departmentOptions(): Collection
    {
        return $this->departments->optionsWithPositions();
    }

    /** @return array{exists: bool, message: string} */
    public function permanentIdStatus(string $value, mixed $ignoreId): array
    {
        if ($value === '') {
            return ['exists' => false, 'message' => ''];
        }

        $exists = $this->employees->permanentIdTaken($value, filled($ignoreId) ? (int) $ignoreId : null);

        return ['exists' => $exists, 'message' => $exists ? 'ID already exists in database.' : 'ID is available.'];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Employee
    {
        $employee = DB::transaction(function () use ($data): Employee {
            $next = $this->employees->highestIdForUpdate() + 1;

            return $this->employees->create([
                'employee_id' => 'EMP-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT),
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
     * Saves the profile fields and the photo (removed, cropped data URI, or uploaded file).
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException when the cropped image is invalid
     */
    public function updateProfile(Employee $employee, array $data, bool $removeProfilePicture, ?string $croppedImage, ?UploadedFile $profilePicture): Employee
    {
        $employeeData = array_intersect_key($data, array_flip(self::PROFILE_FIELDS));
        $before = $employee->only(array_keys($employeeData));

        foreach (['date_hired', 'date_of_birth'] as $dateField) {
            if (array_key_exists($dateField, $employeeData)) {
                $employeeData[$dateField] = $this->normalizeDate($employeeData[$dateField]);
            }
        }

        $this->employees->update($employee, $employeeData);
        $changed = $this->diffChanges($before, $employee->fresh()->only(array_keys($employeeData)));
        $asset = $this->employees->assetOf($employee);

        if ($removeProfilePicture) {
            $this->deleteFile($asset->profile_picture);
            $asset->forceFill(['profile_picture' => null])->save();
            $changed['profile_picture'] = ['from' => 'existing', 'to' => null];
        }

        if (filled($croppedImage)) {
            $path = $this->storeCroppedPhoto($employee, $croppedImage, $asset->profile_picture);
            if ($path !== null) {
                $asset->forceFill(['profile_picture' => $path])->save();
                $changed['profile_picture'] = ['from' => 'existing', 'to' => $path];
            }
        } elseif ($profilePicture !== null) {
            $this->deleteFile($asset->profile_picture);
            $name = 'profile_'.$employee->id.'_'.time().'.'.$profilePicture->getClientOriginalExtension();
            $path = $profilePicture->storeAs('employees', $name, self::DISK);
            $asset->forceFill(['profile_picture' => $path])->save();
            $changed['profile_picture'] = ['from' => 'existing', 'to' => $path];
        }

        $this->audit->log($employee, 'updated_profile', ['changed' => $changed]);

        return $employee->fresh(['asset']);
    }

    /** @param array<string, mixed> $data */
    public function updateStatusDetails(Employee $employee, array $data): Employee
    {
        $before = $employee->only(array_keys($data));
        foreach (['date_resigned', 'last_duty', 'clearance_date', 'last_pay_date'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->normalizeDate($data[$field]);
            }
        }

        $this->employees->update($employee, $data);
        $this->audit->log($employee, 'updated_status_details', [
            'changed' => $this->diffChanges($before, $employee->fresh()->only(array_keys($data))),
        ]);

        return $employee->fresh();
    }

    /**
     * Government ID numbers (a changed number stamps its date, or the date given) and the
     * 201 documents (replacing the old file).
     *
     * @param  array<string, mixed>  $data
     */
    public function updateAssets(Employee $employee, array $data): void
    {
        DB::transaction(function () use ($employee, $data): void {
            $asset = $this->employees->assetOf($employee);
            $before = $asset->only(self::ASSET_AUDIT_FIELDS);

            foreach (array_keys(Employee::GOVERNMENT_IDS) as $key) {
                [$numberField, $dateField] = ["{$key}_number", "{$key}_updated_at"];
                $newNumber = ($data[$numberField] ?? null) === '' ? null : ($data[$numberField] ?? null);
                $manualDate = filled($data[$dateField] ?? null) ? Carbon::parse($data[$dateField])->startOfDay() : null;
                if ($asset->{$numberField} != $newNumber) {
                    $asset->{$numberField} = $newNumber;
                    $asset->{$dateField} = $manualDate ?? now();
                } elseif ($manualDate !== null) {
                    $asset->{$dateField} = $manualDate;
                }
            }

            foreach (array_keys(Employee::DOCUMENTS) as $document) {
                $file = $data[$document] ?? null;
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $this->deleteFile($asset->{$document});
                $name = $document.'_'.$employee->id.'_'.time().'.'.strtolower($file->getClientOriginalExtension());
                $asset->{$document} = $file->storeAs('employees/201', $name, self::DISK);
                $asset->{$document.'_updated_at'} = now();
            }

            $asset->save();
            $changed = $this->diffChanges($before, $asset->fresh()->only(array_keys($before)));
            if ($changed !== []) {
                $this->audit->log($employee, 'updated_201_file', ['changed' => $changed]);
            }
        });
    }

    /** Links (or, with null, unlinks) the employee's biometric record. */
    public function linkBiometric(Employee $employee, ?int $biometricId): void
    {
        $this->employees->update($employee, ['employee_biometric_id' => $biometricId]);
    }

    public function delete(Employee $employee): void
    {
        DB::transaction(function () use ($employee): void {
            $this->audit->log($employee, 'deleted', [
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->full_name,
            ]);
            $this->employees->delete($employee);
        });
    }

    public function find(int $id): Employee
    {
        return $this->employees->findOrFail($id);
    }

    /** Employee with everything the 201 PDF prints. */
    public function forPdf(int $id): Employee
    {
        return $this->employees->findOrFail($id, ['asset', 'histories', 'attachments', 'position', 'department']);
    }

    /** The profile photo as a data URI for the PDF, or null. */
    public function profilePictureDataUri(Employee $employee): ?string
    {
        $path = $employee->asset?->profile_picture;
        if (! $path || ! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        $mime = Storage::disk(self::DISK)->mimeType($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode((string) Storage::disk(self::DISK)->get($path));
    }

    /** Absolute path of the profile photo; 404 when there is none. */
    public function profilePicturePath(Employee $employee): string
    {
        return $this->existingPath($employee->asset?->profile_picture);
    }

    /** Absolute path of a 201 document (birth_certificate, resume, contract); 404 when missing. */
    public function documentPath(Employee $employee, string $document): string
    {
        return $this->existingPath(array_key_exists($document, Employee::DOCUMENTS) ? $employee->asset?->{$document} : null);
    }

    /** Stores a cropped "data:image/...;base64," photo; null when the value is not a data URI. */
    private function storeCroppedPhoto(Employee $employee, string $croppedImage, ?string $oldPath): ?string
    {
        if (strlen($croppedImage) > 4 * 1024 * 1024) {
            throw ValidationException::withMessages(['profile_picture_cropped' => 'The cropped image is too large.']);
        }

        if (preg_match('/^data:image\/\w+;base64,/', $croppedImage) !== 1) {
            return null;
        }

        $binary = base64_decode(substr($croppedImage, strpos($croppedImage, ',') + 1), true);
        if ($binary === false || strlen($binary) > 2 * 1024 * 1024 || @getimagesizefromstring($binary) === false) {
            throw ValidationException::withMessages(['profile_picture_cropped' => 'The cropped image is invalid or too large.']);
        }

        $this->deleteFile($oldPath);
        $cleanName = strtolower((string) preg_replace('/[^a-z0-9_-]+/i', '_', $employee->full_name));
        $permanentId = $employee->employee_id_permanent ?? $employee->id;
        $path = "employees/{$cleanName}_{$permanentId}.jpg";
        Storage::disk(self::DISK)->put($path, $binary);

        return $path;
    }

    private function existingPath(?string $path): string
    {
        abort_unless(filled($path) && Storage::disk(self::DISK)->exists($path), 404);

        return Storage::disk(self::DISK)->path($path);
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
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
        return blank($value) ? null : Carbon::parse((string) $value)->format('Y-m-d');
    }
}
