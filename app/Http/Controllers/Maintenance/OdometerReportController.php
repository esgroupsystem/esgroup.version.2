<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\BusDetail;
use App\Models\DieselStock;
use App\Models\OdometerSubmission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $perPage = 15;

        $buses = BusDetail::query()
            ->select('id', 'garage', 'name', 'body_number', 'plate_number')
            ->orderBy('body_number')
            ->orderBy('plate_number')
            ->get();

        $selectedBus = $busId ? BusDetail::find($busId) : null;

        $baseSubmissionsQuery = OdometerSubmission::query()
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
            ]);

        $allSubmissions = (clone $baseSubmissionsQuery)
            ->orderBy('odometer_submissions.bus_detail_id')
            ->orderBy('odometer_submissions.date')
            ->orderBy('odometer_submissions.time')
            ->orderBy('odometer_submissions.id')
            ->get();

        $allRecords = $this->buildOdometerRecords(
            submissions: $allSubmissions,
            startDate: $startDate,
            lastChangeOilKm: $lastChangeOilKm,
            changeOilEvery: $changeOilEvery
        );

        $submissions = (clone $baseSubmissionsQuery)
            ->orderBy('odometer_submissions.bus_detail_id')
            ->orderBy('odometer_submissions.date')
            ->orderBy('odometer_submissions.time')
            ->orderBy('odometer_submissions.id')
            ->paginate($perPage)
            ->withQueryString();

        $pageIds = $submissions->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $records = $allRecords
            ->whereIn('id', $pageIds)
            ->values();

        $totalKm = $allRecords->sum('total_km_run');
        $totalLiters = $allRecords->sum('diesel_consumption');
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
            ? (float) $validated['liters'] * (float) $validated['unit_cost']
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
        $newOdometer = (int) $validated['new_odometer'];
        $busDetailId = (int) $validated['bus_detail_id'];
        $dieselConsumption = (float) ($validated['diesel_consumption'] ?? 0);

        $result = DB::transaction(function () use (
            $validated,
            $request,
            $manualDate,
            $manualTime,
            $newOdometer,
            $busDetailId,
            $dieselConsumption
        ) {
            $duplicateSubmissionExists = OdometerSubmission::query()
                ->where('bus_detail_id', $busDetailId)
                ->whereDate('date', $manualDate)
                ->whereTime('time', $manualTime)
                ->lockForUpdate()
                ->exists();

            if ($duplicateSubmissionExists) {
                return [
                    'saved' => false,
                    'message' => 'This bus already has an odometer record with the same date and time. Adjust the time by at least 1 minute.',
                ];
            }

            $previousSubmission = $this->findPreviousSubmission(
                busDetailId: $busDetailId,
                date: $manualDate,
                time: $manualTime,
                lockForUpdate: true
            );

            if (
                $previousSubmission &&
                $newOdometer < (int) $previousSubmission->new_odometer
            ) {
                return [
                    'saved' => false,
                    'message' => 'New odometer cannot be lower than the previous odometer reading of '
                        .number_format((int) $previousSubmission->new_odometer).' km.',
                ];
            }

            $nextSubmission = $this->findNextSubmission(
                busDetailId: $busDetailId,
                date: $manualDate,
                time: $manualTime,
                lockForUpdate: true
            );

            if (
                $nextSubmission &&
                $newOdometer > (int) $nextSubmission->new_odometer
            ) {
                return [
                    'saved' => false,
                    'message' => 'New odometer cannot be higher than the next odometer reading of '
                        .number_format((int) $nextSubmission->new_odometer).' km on '
                        .Carbon::parse($nextSubmission->date)->format('M d, Y').' '
                        .Carbon::parse($nextSubmission->time)->format('g:i A').'.',
                ];
            }

            $submission = OdometerSubmission::create([
                'user_id' => Auth::id(),
                'bus_detail_id' => $busDetailId,
                'new_odometer' => $newOdometer,
                'driver_name' => $validated['driver_name'] ?? null,
                'diesel_consumption' => $dieselConsumption,
                'date_bus_deployed' => $validated['date_bus_deployed'] ?? $manualDate,
                'date' => $manualDate,
                'time' => $manualTime,
            ]);

            if (
                $request->boolean('also_deduct_diesel_stock') &&
                $dieselConsumption > 0
            ) {
                DieselStock::create([
                    'date' => $manualDate,
                    'type' => 'out',
                    'liters' => $dieselConsumption,
                    'unit_cost' => null,
                    'total_cost' => null,
                    'bus_detail_id' => $busDetailId,
                    'reference_no' => 'ODO-'.$submission->id,
                    'remarks' => 'Auto diesel OUT from manual odometer encoding.',
                    'encoded_by' => Auth::id(),
                ]);
            }

            return [
                'saved' => true,
                'message' => 'Manual odometer record saved successfully.',
            ];
        });

        if (! $result['saved']) {
            return back()
                ->withInput()
                ->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    public function destroyOdometer(OdometerSubmission $odometerSubmission)
    {
        DB::transaction(function () use ($odometerSubmission) {
            DieselStock::where('reference_no', 'ODO-'.$odometerSubmission->id)
                ->where('type', 'out')
                ->where('bus_detail_id', $odometerSubmission->bus_detail_id)
                ->delete();

            $odometerSubmission->delete();
        });

        return back()->with('success', 'Odometer record deleted successfully.');
    }

    private function buildOdometerRecords(
        Collection $submissions,
        Carbon $startDate,
        ?int $lastChangeOilKm,
        int $changeOilEvery
    ): Collection {
        $previousOdometers = [];

        $busIdsForPrevious = $submissions
            ->pluck('bus_detail_id')
            ->filter()
            ->unique()
            ->values();

        foreach ($busIdsForPrevious as $submissionBusId) {
            $previousOdometers[(int) $submissionBusId] = OdometerSubmission::query()
                ->where('bus_detail_id', (int) $submissionBusId)
                ->whereDate('date', '<', $startDate->toDateString())
                ->orderByDesc('date')
                ->orderByDesc('time')
                ->orderByDesc('id')
                ->value('new_odometer');
        }

        return $submissions->map(function ($row) use (&$previousOdometers, $lastChangeOilKm, $changeOilEvery) {
            $currentBusId = (int) $row->bus_detail_id;
            $newOdometer = (int) $row->new_odometer;
            $previousOdometer = $previousOdometers[$currentBusId] ?? null;

            $totalKmRun = $previousOdometer !== null
                ? $newOdometer - (int) $previousOdometer
                : 0;

            $diesel = (float) ($row->diesel_consumption ?? 0);

            $kmPerLiter = $diesel > 0 && $totalKmRun > 0
                ? $totalKmRun / $diesel
                : 0;

            $baseChangeOilKm = $lastChangeOilKm ?: $newOdometer;
            $nextChangeOil = $baseChangeOilKm + $changeOilEvery;
            $remainingKm = $nextChangeOil - $newOdometer;

            $previousOdometers[$currentBusId] = $newOdometer;

            return [
                'id' => (int) $row->id,
                'garage' => $row->garage,
                'bus_name' => $row->bus_name,
                'body_number' => $row->body_number,
                'plate_number' => $row->plate_number,
                'date' => $row->date,
                'time' => $row->time,
                'driver_name' => $row->driver_name ?: 'N/A',
                'previous_odometer' => $previousOdometer,
                'new_odometer' => $newOdometer,
                'total_km_run' => $totalKmRun,
                'diesel_consumption' => $diesel,
                'km_per_liter' => $kmPerLiter,
                'remaining_change_oil' => $remainingKm,
            ];
        });
    }

    private function findPreviousSubmission(
        int $busDetailId,
        string $date,
        string $time,
        bool $lockForUpdate = false
    ): ?OdometerSubmission {
        $query = OdometerSubmission::query()
            ->where('bus_detail_id', $busDetailId)
            ->where(function ($query) use ($date, $time) {
                $query->whereDate('date', '<', $date)
                    ->orWhere(function ($q) use ($date, $time) {
                        $q->whereDate('date', $date)
                            ->whereTime('time', '<', $time);
                    });
            })
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->orderByDesc('id');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function findNextSubmission(
        int $busDetailId,
        string $date,
        string $time,
        bool $lockForUpdate = false
    ): ?OdometerSubmission {
        $query = OdometerSubmission::query()
            ->where('bus_detail_id', $busDetailId)
            ->where(function ($query) use ($date, $time) {
                $query->whereDate('date', '>', $date)
                    ->orWhere(function ($q) use ($date, $time) {
                        $q->whereDate('date', $date)
                            ->whereTime('time', '>', $time);
                    });
            })
            ->orderBy('date')
            ->orderBy('time')
            ->orderBy('id');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }
}
