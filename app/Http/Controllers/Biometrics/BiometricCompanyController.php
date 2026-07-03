<?php

namespace App\Http\Controllers\Biometrics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Biometrics\StoreBiometricCompanyRequest;
use App\Services\Biometrics\BiometricCompanyService;
use Illuminate\Http\RedirectResponse;

class BiometricCompanyController extends Controller
{
    public function __construct(
        protected BiometricCompanyService $biometricCompanyService,
    ) {}

    public function store(StoreBiometricCompanyRequest $request): RedirectResponse
    {
        $this->biometricCompanyService->create($request->validated());

        return back()->with('success', 'Biometric company added successfully.');
    }
}
