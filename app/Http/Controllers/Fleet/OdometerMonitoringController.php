<?php

declare(strict_types=1);

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\StoreDieselStockRequest;
use App\Http\Requests\Fleet\StoreManualOdometerRequest;
use App\Http\Requests\Fleet\UpdateOdometerRequest;
use App\Models\BusDetail;
use App\Models\DieselStock;
use App\Models\OdometerSubmission;
use App\Services\Fleet\OdometerExportService;
use App\Services\Fleet\OdometerPeriod;
use App\Services\Fleet\OdometerService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Fleet → Odometer Monitoring (odometer.*). */
final class OdometerMonitoringController extends Controller
{
    private const PER_PAGE = 15;

    public function __construct(
        private readonly OdometerService $odometerService,
        private readonly OdometerExportService $exportService,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $period = OdometerPeriod::fromRequest($request);
        $busId = $request->filled('bus_detail_id') ? (int) $request->input('bus_detail_id') : null;
        $lastChangeOil = $request->filled('last_change_oil') ? (int) $request->input('last_change_oil') : null;
        $buses = $this->odometerService->busOptions();
        $selectedBus = $busId ? $buses->firstWhere('id', $busId) : null;
        $records = $this->odometerService->records($period, $busId, $lastChangeOil);
        $summary = $this->odometerService->summary($period, $records);
        $user = $request->user();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $records->forPage($page, self::PER_PAGE)->values(),
            $records->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('dashboards/odometer/index', [
            'filters' => [
                'filter_type' => $period->normalizedType(),
                'month' => $period->month,
                'date' => $period->date,
                'date_from' => $period->dateFrom,
                'date_to' => $period->dateTo,
                'bus_detail_id' => $busId ? (string) $busId : '',
                'last_change_oil' => $lastChangeOil !== null ? (string) $lastChangeOil : '',
            ],
            'periodLabel' => $period->label,
            'selectedBus' => $selectedBus ? trim(($selectedBus->body_number ?? '').' - '.($selectedBus->name ?? '')) : null,
            'buses' => $buses->map(fn (BusDetail $bus): array => [
                'value' => (string) $bus->id,
                'label' => trim(($bus->body_number ?? 'N/A').' - '.($bus->name ?? '').' · '.($bus->plate_number ?? '-').' · '.($bus->garage ?? '-')),
            ])->values(),
            'summary' => [
                'current_stock' => round($summary['current_stock'], 2),
                'period_in' => round($summary['period_in'], 2),
                'period_out' => round($summary['period_out'], 2),
                'period_adjustment' => round($summary['period_adjustment'], 2),
                'total_km' => $summary['total_km'],
                'total_liters' => round($summary['total_liters'], 2),
                'average_km_per_liter' => round($summary['average_km_per_liter'], 2),
            ],
            'records' => [
                ...collect($paginator->toArray())->except('data')->all(),
                'data' => $paginator->getCollection()->map(fn (array $row): array => [
                    ...$row,
                    'date_label' => Carbon::parse($row['date'])->format('M d, Y'),
                    'date' => Carbon::parse($row['date'])->toDateString(),
                    'time_label' => Carbon::parse($row['time'])->format('g:i A'),
                    'time' => Carbon::parse($row['time'])->format('H:i'),
                    'date_bus_deployed' => $row['date_bus_deployed'] ? Carbon::parse($row['date_bus_deployed'])->toDateString() : null,
                    'km_per_liter' => round((float) $row['km_per_liter'], 2),
                    'update_url' => route('odometer.update', $row['id']),
                    'destroy_url' => route('odometer.destroy', $row['id']),
                ])->values(),
            ],
            'movements' => $this->odometerService->movements($period, $busId)->map(fn (DieselStock $stock): array => [
                'id' => $stock->id,
                'date' => $stock->date?->format('M d, Y') ?? '-',
                'type' => $stock->type,
                'reference_no' => $stock->reference_no,
                'bus' => $stock->bus ? trim(($stock->bus->body_number ?? '').' - '.($stock->bus->name ?? '')) : null,
                'liters' => (float) $stock->liters,
                'unit_cost' => $stock->unit_cost !== null ? (float) $stock->unit_cost : null,
                'total_cost' => $stock->total_cost !== null ? (float) $stock->total_cost : null,
                'remarks' => $stock->remarks,
                'encoder' => $stock->encoder?->full_name,
            ])->values(),
            'charts' => ['daily' => $this->daily($records), 'perBus' => $this->perBus($records)],
            // Mirrors the route middleware on each odometer.* write route.
            'can' => [
                'create' => (bool) $user?->can('odometer.create'),
                'edit' => (bool) $user?->can('odometer.edit'),
                'delete' => (bool) $user?->can('odometer.delete'),
                'diesel' => (bool) $user?->can('odometer.update'),
            ],
            'urls' => [
                'index' => route('odometer.index'),
                'export' => route('odometer.export'),
                'manual' => route('odometer.manual.store'),
                'diesel' => route('odometer.diesel-stock.store'),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse|Response
    {
        $period = OdometerPeriod::fromRequest($request);
        $busId = $request->filled('bus_detail_id') ? (int) $request->input('bus_detail_id') : null;
        $lastChangeOil = $request->filled('last_change_oil') ? (int) $request->input('last_change_oil') : null;
        $bus = $busId ? $this->odometerService->busOptions()->firstWhere('id', $busId) : null;
        $records = $this->odometerService->records($period, $busId, $lastChangeOil);

        return $this->exportService->download(
            OdometerExportService::type($request->string('export_type')->toString()),
            $period,
            $records,
            $this->odometerService->movements($period, $busId),
            [
                ...$this->odometerService->summary($period, $records),
                'period_label' => $period->label,
                'selected_bus' => $bus ? trim(($bus->body_number ?? '').' - '.($bus->name ?? '').' - '.($bus->garage ?? '')) : 'All Bus Units',
                'last_change_oil_km' => $lastChangeOil,
            ],
        );
    }

    public function storeDieselStock(StoreDieselStockRequest $request): RedirectResponse
    {
        $this->odometerService->storeDieselStock($request->validated(), $request->user()?->id);

        return back()->with('success', 'Diesel stock record saved successfully.');
    }

    public function storeManualOdometer(StoreManualOdometerRequest $request): RedirectResponse
    {
        $refusal = $this->odometerService->storeManual($request->validated(), $request->boolean('also_deduct_diesel_stock'), $request->user()?->id);

        if ($refusal !== null) {
            return back()->withInput()->with('error', $refusal);
        }

        return back()->with('success', 'Manual odometer record saved successfully.');
    }

    public function updateOdometer(UpdateOdometerRequest $request, OdometerSubmission $odometerSubmission): RedirectResponse
    {
        $this->odometerService->update($odometerSubmission, $request->validated());

        return back()->with('success', 'Odometer record updated successfully.');
    }

    public function destroyOdometer(OdometerSubmission $odometerSubmission): RedirectResponse
    {
        $this->odometerService->delete($odometerSubmission);

        return back()->with('success', 'Odometer record deleted successfully.');
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $records
     * @return Collection<int, array{label: string, km: int, liters: float}>
     */
    private function daily(Collection $records): Collection
    {
        return $records
            ->groupBy(fn (array $row): string => Carbon::parse($row['date'])->toDateString())
            ->sortKeys()
            ->map(fn (Collection $rows, string $date): array => [
                'label' => Carbon::parse($date)->format('M d'),
                'km' => (int) $rows->sum('total_km_run'),
                'liters' => round((float) $rows->sum('diesel_consumption'), 2),
            ])
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $records
     * @return Collection<int, array{label: string, value: int}> top 10 buses by km run
     */
    private function perBus(Collection $records): Collection
    {
        return $records
            ->groupBy('bus_detail_id')
            ->map(fn (Collection $rows): array => [
                'label' => trim(($rows->first()['body_number'] ?? 'N/A').' '.($rows->first()['bus_name'] ?? '')),
                'value' => (int) $rows->sum('total_km_run'),
            ])
            ->sortByDesc('value')
            ->take(10)
            ->values();
    }
}
