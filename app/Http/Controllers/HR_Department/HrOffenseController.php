<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\HrOffense;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HrOffenseController extends Controller
{
    public const TYPES = ['A', 'B', 'C', 'D', 'E', 'F'];

    public const GRAVITIES = ['CAPITAL', 'GRAVE', 'SEVERE', 'MINOR', 'LIGHT', 'SERIOUS', 'FALSE'];

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $type = in_array($request->query('type'), self::TYPES, true) ? (string) $request->query('type') : '';
        $gravity = in_array($request->query('gravity'), self::GRAVITIES, true) ? (string) $request->query('gravity') : '';

        $query = HrOffense::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner
                ->where('section', 'like', "%{$search}%")
                ->orWhere('offense_description', 'like', "%{$search}%")))
            ->when($type !== '', fn ($q) => $q->where('offense_type', $type))
            ->when($gravity !== '', fn ($q) => $q->where('offense_gravity', $gravity));

        // Filter by ID
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        $offenses = $query
            ->orderBy('id') // important for numeric order
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('hr/offenses/index', [
            'offenses' => $offenses->through(fn (HrOffense $offense): array => [
                'id' => $offense->id,
                'section' => $offense->section,
                'offense_description' => $offense->offense_description,
                'offense_type' => $offense->offense_type,
                'offense_gravity' => $offense->offense_gravity,
            ]),
            'filters' => ['search' => $search, 'type' => $type, 'gravity' => $gravity],
            'types' => self::TYPES,
            'gravities' => self::GRAVITIES,
            'can' => ['create' => (bool) $request->user()?->can('violations.create')],
            'urls' => ['index' => route('violation.offenses.index'), 'store' => route('violation.offenses.store')],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section' => 'required|string|max:255',
            'offense_description' => 'required|string',
            'offense_type' => 'required|string',
            'offense_gravity' => 'required|string',
        ]);

        HrOffense::create($validated);

        return back()->with('success', 'Offense saved successfully.');
    }
}
