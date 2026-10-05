<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreHrOffenseRequest;
use App\Http\Resources\HR\HrOffenseResource;
use App\Models\HrOffense;
use App\Services\HR\HrOffenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Human Resources → HR Offenses.
 */
final class HrOffenseController extends Controller
{
    public function __construct(
        private readonly HrOffenseService $offenses,
    ) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $type = $this->offenses->normalizeFilter($request->query('type'), HrOffense::TYPES);
        $gravity = $this->offenses->normalizeFilter($request->query('gravity'), HrOffense::GRAVITIES);
        $id = $request->filled('id') ? $request->integer('id') : null;

        return Inertia::render('hr/offenses/index', [
            'offenses' => $this->offenses->paginate($search, $type, $gravity, $id)
                ->through(fn (HrOffense $offense): array => HrOffenseResource::make($offense)->resolve($request)),
            'filters' => ['search' => $search, 'type' => $type, 'gravity' => $gravity],
            'types' => HrOffense::TYPES,
            'gravities' => HrOffense::GRAVITIES,
            'can' => ['create' => (bool) $request->user()?->can('violations.create')],
            'urls' => ['index' => route('violation.offenses.index'), 'store' => route('violation.offenses.store')],
        ]);
    }

    public function store(StoreHrOffenseRequest $request): RedirectResponse
    {
        $this->offenses->create($request->validated());

        return back()->with('success', 'Offense saved successfully.');
    }
}
