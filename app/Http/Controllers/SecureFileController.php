<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ConductorLeave;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SecureFileController extends Controller
{
    public function employeeProfile(Employee $employee): StreamedResponse
    {
        $asset = $employee->asset;
        abort_unless($asset !== null, 404);

        $path = (string) $asset->profile_picture;
        abort_unless($path !== '', 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, basename($path), [
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function employeeAssetFile(Employee $employee, string $type): StreamedResponse
    {
        $path = match ($type) {
            'birth_certificate' => $employee->asset?->birth_certificate,
            'resume' => $employee->asset?->resume,
            'contract' => $employee->asset?->contract,
            default => null,
        };

        return $this->responseFor($path);
    }

    public function employeeLeaveProof(EmployeeLeave $leave, string $type): StreamedResponse
    {
        $path = match ($type) {
            'first' => $leave->first_notice_proof,
            'second' => $leave->second_notice_proof,
            'final' => $leave->final_notice_proof,
            default => null,
        };

        return $this->responseFor($path);
    }

    public function driverLeaveProof(DriverLeave $leave, string $type): StreamedResponse
    {
        $path = match ($type) {
            'first' => $leave->first_notice_proof,
            'second' => $leave->second_notice_proof,
            'final' => $leave->final_notice_proof,
            default => null,
        };

        return $this->responseFor($path);
    }

    public function conductorLeaveProof(ConductorLeave $leave, string $type): StreamedResponse
    {
        $path = match ($type) {
            'first' => $leave->first_notice_proof,
            'second' => $leave->second_notice_proof,
            'final' => $leave->final_notice_proof,
            default => null,
        };

        return $this->responseFor($path);
    }

    private function responseFor(?string $path): StreamedResponse
    {
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, basename($path), [
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
