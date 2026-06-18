<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\BusDetail;
use App\Models\DieselStock;
use App\Models\OdometerSubmission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OdometerReportController extends Controller
{
    public function index(Request $request)
    {
        $busId = $request->filled('bus_detail_id') ? (int) $request->bus_detail_id : null;

        $filterType = $request->get('filter_type', 'month');
        $month = $request->month ?: now()->format('Y-m');
        $selectedDate = $request->date ?: now()->toDateString();
        $dateFrom = $request->date_from ?: now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?: now()->endOfMonth()->toDateString();

        if ($filterType === 'day') {
            $startDate = Carbon::parse($selectedDate)->startOfDay();
            $endDate = Carbon::parse($selectedDate)->endOfDay();
            $periodLabel = $startDate->format('F d, Y');
        } elseif ($filterType === 'range') {
            $startDate = Carbon::parse($dateFrom)->startOfDay();
            $endDate = Carbon::parse($dateTo)->endOfDay();
            $periodLabel = $startDate->format('M d, Y').' - '.$endDate->format('M d, Y');
        } else {
            $monthDate = Carbon::createFromFormat('Y-m-d', $month.'-01');
            $startDate = $monthDate->copy()->startOfMonth();
            $endDate = $monthDate->copy()->endOfMonth();
            $periodLabel = $monthDate->format('F Y');
        }

        $lastChangeOilKm = $request->filled('last_change_oil') ? (int) $request->last_change_oil : null;
        $changeOilEvery = 10000;

        $buses = BusDetail::query()
            ->select('id', 'garage', 'name', 'body_number', 'plate_number')
            ->orderBy('body_number')
            ->orderBy('plate_number')
            ->get();

        $selectedBus = $busId ? BusDetail::find($busId) : null;

        $perPage = 15; // Number of records per page

        $submissions = OdometerSubmission::query()
            ->leftJoin('bus_details', 'odometer_submissions.bus_detail_id', '=', 'bus_details.id')
            ->select(
                'odometer_submissions.id',
                'odometer_submissions.bus_detail_id',
                'odometer_submissions.date',
                'odometer_submissions.time',
                'odometer_submissions.driver_name',
                'odometer_submissions.new_odometer',
                'odometer_submissions.diesel_consumption',
                'bus_details.garage',
                'bus_details.name as bus_name',
                'bus_details.body_number',
                'bus_details.plate_number'
            )
            ->when($busId, function ($query) use ($busId) {
                $query->where('odometer_submissions.bus_detail_id', $busId);
            })
            ->whereBetween('odometer_submissions.date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->orderBy('odometer_submissions.bus_detail_id')
            ->orderBy('odometer_submissions.date')
            ->orderBy('odometer_submissions.time')
            ->orderBy('odometer_submissions.id')
            ->paginate($perPage)
            ->withQueryString();

        $previousOdometers = [];

        $busIdsForPrevious = $submissions
            ->pluck('bus_detail_id')
            ->filter()
            ->unique()
            ->values();

        foreach ($busIdsForPrevious as $submissionBusId) {
            $previousOdometers[$submissionBusId] = OdometerSubmission::where('bus_detail_id', $submissionBusId)
                ->whereDate('date', '<', $startDate->toDateString())
                ->orderByDesc('date')
                ->orderByDesc('time')
                ->orderByDesc('id')
                ->value('new_odometer');
        }

        $records = $submissions->map(function ($row) use (&$previousOdometers, $lastChangeOilKm, $changeOilEvery) {
            $currentBusId = (int) $row->bus_detail_id;

            $previousOdometer = $previousOdometers[$currentBusId] ?? null;

            $totalKmRun = $previousOdometer
                ? ((int) $row->new_odometer - (int) $previousOdometer)
                : 0;

            $totalKmRun = max($totalKmRun, 0);

            $diesel = (float) ($row->diesel_consumption ?? 0);

            $kmPerLiter = $diesel > 0
                ? $totalKmRun / $diesel
                : 0;

            $baseChangeOilKm = $lastChangeOilKm ?: (int) $row->new_odometer;
            $nextChangeOil = $baseChangeOilKm + $changeOilEvery;
            $remainingKm = $nextChangeOil - (int) $row->new_odometer;

            $previousOdometers[$currentBusId] = (int) $row->new_odometer;

            return [
                'id' => $row->id,
                'garage' => $row->garage,
                'bus_name' => $row->bus_name,
                'body_number' => $row->body_number,
                'plate_number' => $row->plate_number,
                'date' => $row->date,
                'time' => $row->time,
                'driver_name' => $row->driver_name,
                'previous_odometer' => $previousOdometer,
                'new_odometer' => $row->new_odometer,
                'total_km_run' => $totalKmRun,
                'diesel_consumption' => $diesel,
                'km_per_liter' => $kmPerLiter,
                'remaining_change_oil' => $remainingKm,
            ];
        });

        $totalKm = $records->sum('total_km_run');
        $totalLiters = $records->sum('diesel_consumption');
        $averageKmPerLiter = $totalLiters > 0 ? $totalKm / $totalLiters : 0;

        $overallDieselIn = DieselStock::where('type', 'in')->sum('liters');
        $overallDieselOut = DieselStock::where('type', 'out')->sum('liters');
        $overallDieselAdjustment = DieselStock::where('type', 'adjustment')->sum('liters');
        $currentDieselStock = $overallDieselIn - $overallDieselOut + $overallDieselAdjustment;

        $periodDieselIn = DieselStock::where('type', 'in')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('liters');

        $periodDieselOut = DieselStock::where('type', 'out')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('liters');

        $periodDieselAdjustment = DieselStock::where('type', 'adjustment')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('liters');

        $dieselStockMovements = DieselStock::with(['bus:id,garage,name,body_number,plate_number', 'encoder:id,full_name'])
            ->when($busId, function ($query) use ($busId) {
                $query->where(function ($q) use ($busId) {
                    $q->where('bus_detail_id', $busId)
                        ->orWhereNull('bus_detail_id');
                });
            })
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return view('maintenance.odometer.report', compact(
            'records',
            'submissions',
            'buses',
            'busId',
            'selectedBus',
            'lastChangeOilKm',
            'filterType',
            'month',
            'selectedDate',
            'dateFrom',
            'dateTo',
            'periodLabel',
            'totalKm',
            'totalLiters',
            'averageKmPerLiter',
            'currentDieselStock',
            'periodDieselIn',
            'periodDieselOut',
            'periodDieselAdjustment',
            'dieselStockMovements'
        ));
    }

    public function storeDieselStock(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'type' => ['required', 'in:in,out,adjustment'],
            'liters' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'bus_detail_id' => ['nullable', 'exists:bus_details,id'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $validated['total_cost'] = ! empty($validated['unit_cost'])
            ? $validated['liters'] * $validated['unit_cost']
            : null;

        $validated['encoded_by'] = Auth::id();

        DieselStock::create($validated);

        return back()->with('success', 'Diesel stock record saved successfully.');
    }

    public function storeManualOdometer(Request $request)
    {
        $validated = $request->validate([
            'bus_detail_id' => ['required', 'exists:bus_details,id'],
            'date_bus_deployed' => ['nullable', 'date'],
            'date' => ['required', 'date'],
            'time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'new_odometer' => ['required', 'integer', 'min:0'],
            'diesel_consumption' => ['nullable', 'numeric', 'min:0'],
            'also_deduct_diesel_stock' => ['nullable', 'boolean'],
        ]);

        $manualDate = Carbon::parse($validated['date'])->toDateString();
        $manualTime = Carbon::parse($validated['time'])->format('H:i:s');

        $previousSubmission = OdometerSubmission::where('bus_detail_id', $validated['bus_detail_id'])
            ->where(function ($query) use ($manualDate, $manualTime) {
                $query->whereDate('date', '<', $manualDate)
                    ->orWhere(function ($q) use ($manualDate, $manualTime) {
                        $q->whereDate('date', $manualDate)
                            ->whereTime('time', '<', $manualTime);
                    });
            })
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->orderByDesc('id')
            ->first();

        if (
            $previousSubmission &&
            (int) $validated['new_odometer'] < (int) $previousSubmission->new_odometer
        ) {
            return back()
                ->withInput()
                ->with('error', 'New odometer cannot be lower than the previous odometer reading of '
                    .number_format($previousSubmission->new_odometer).' km.');
        }

        DB::transaction(function () use ($validated, $request, $manualDate, $manualTime) {
            $submission = OdometerSubmission::create([
                'user_id' => Auth::id(),
                'bus_detail_id' => $validated['bus_detail_id'],
                'new_odometer' => $validated['new_odometer'],
                'driver_name' => $validated['driver_name'] ?? null,
                'diesel_consumption' => $validated['diesel_consumption'] ?? 0,
                'date_bus_deployed' => $validated['date_bus_deployed'] ?? $manualDate,
                'date' => $manualDate,
                'time' => $manualTime,
            ]);

            /*
             * Optional:
             * If checked, this will also deduct diesel stock automatically.
             * Do not check this if you already encode diesel OUT separately.
             */
            if (
                $request->boolean('also_deduct_diesel_stock') &&
                (float) ($validated['diesel_consumption'] ?? 0) > 0
            ) {
                DieselStock::create([
                    'date' => $manualDate,
                    'type' => 'out',
                    'liters' => $validated['diesel_consumption'],
                    'unit_cost' => null,
                    'total_cost' => null,
                    'bus_detail_id' => $validated['bus_detail_id'],
                    'reference_no' => 'ODO-'.$submission->id,
                    'remarks' => 'Auto diesel OUT from manual odometer encoding.',
                    'encoded_by' => Auth::id(),
                ]);
            }
        });

        return back()->with('success', 'Manual odometer record saved successfully.');
    }
}
