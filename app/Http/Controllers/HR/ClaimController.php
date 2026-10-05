<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\ClaimRequest;
use App\Http\Resources\HR\ClaimResource;
use App\Models\Claim;
use App\Models\Employee;
use App\Services\HR\ClaimService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Human Resources → Benefits → SSS / Maternity / Paternity.
 */
final class ClaimController extends Controller
{
    public function __construct(
        private readonly ClaimService $claims,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $this->claims->filters($request->query());
        $user = $request->user();

        return Inertia::render('hr/claims/index', [
            'claims' => $this->claims->paginate($filters)
                ->through(fn (Claim $claim): array => ClaimResource::make($claim)->resolve($request)),
            'statusCounts' => $this->claims->statusCounts($filters),
            'employees' => $this->claims->employeeOptions()
                ->map(fn (Employee $employee): array => ['value' => (string) $employee->id, 'label' => (string) $employee->full_name])
                ->values(),
            'types' => Claim::TYPES,
            'statuses' => Claim::STATUSES,
            'dateFields' => Claim::DATE_FIELDS,
            'filters' => $filters,
            'can' => [
                'create' => (bool) $user?->can('claims.create'),
                'update' => (bool) $user?->can('claims.update'),
                'delete' => (bool) $user?->can('claims.delete'),
            ],
            'urls' => [
                'index' => route('claims.index'),
                'store' => route('claims.store'),
                'update' => route('claims.update', '__ID__'),
                'destroy' => route('claims.destroy', '__ID__'),
            ],
        ]);
    }

    public function store(ClaimRequest $request): RedirectResponse
    {
        $this->claims->create($request->validated(), $this->userId($request));

        return redirect()->route('claims.index')->with('success', 'Claim created successfully.');
    }

    public function update(ClaimRequest $request, Claim $claim): RedirectResponse
    {
        $this->claims->update($claim, $request->validated(), $this->userId($request));

        return redirect()->route('claims.index')->with('success', 'Claim updated successfully.');
    }

    public function destroy(Claim $claim): RedirectResponse
    {
        $this->claims->delete($claim);

        return redirect()->route('claims.index')->with('success', 'Claim deleted successfully.');
    }

    private function userId(Request $request): ?int
    {
        $id = $request->user()?->getKey();

        return $id === null ? null : (int) $id;
    }
}
