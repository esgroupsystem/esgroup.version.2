<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\BusDetail;
use App\Models\OdometerSubmission;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OdometerReportController extends Controller
{
    public function index(Request $request)
    {
        $busId = $request->filled('bus_detail_id') ? (int) $request->bus_detail_id : null;
        $month = $request->month ?: now()->format('Y-m');
        $lastChangeOilKm = $request->filled('last_change_oil') ? (int) $request->last_change_oil : null;
        $changeOilEvery = 10000;

        $monthDate = Carbon::createFromFormat('Y-m-d', $month.'-01');
        $startOfMonth = $monthDate->copy()->startOfMonth();
        $endOfMonth = $monthDate->copy()->endOfMonth();

        $buses = BusDetail::query()
            ->select('id', 'garage', 'name', 'body_number', 'plate_number')
            ->orderBy('body_number')
            ->orderBy('plate_number')
            ->get();

        $records = collect();
        $selectedBus = null;

        if ($busId) {
            $selectedBus = BusDetail::find($busId);

            $lastOdometer = OdometerSubmission::where('bus_detail_id', $busId)
                ->whereDate('date', '<', $startOfMonth->toDateString())
                ->orderByDesc('date')
                ->orderByDesc('time')
                ->orderByDesc('id')
                ->value('new_odometer');

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
                ->where('odometer_submissions.bus_detail_id', $busId)
                ->whereBetween('odometer_submissions.date', [
                    $startOfMonth->toDateString(),
                    $endOfMonth->toDateString(),
                ])
                ->orderBy('odometer_submissions.date')
                ->orderBy('odometer_submissions.time')
                ->orderBy('odometer_submissions.id')
                ->get();

            $records = $submissions->map(function ($row) use (&$lastOdometer, $lastChangeOilKm, $changeOilEvery) {
                $previousOdometer = $lastOdometer;

                $totalKmRun = $previousOdometer
                    ? ((int) $row->new_odometer - (int) $previousOdometer)
                    : 0;

                $totalKmRun = max($totalKmRun, 0);

                $diesel = (float) $row->diesel_consumption;

                $kmPerLiter = $diesel > 0
                    ? $totalKmRun / $diesel
                    : 0;

                $baseChangeOilKm = $lastChangeOilKm ?: (int) $row->new_odometer;
                $nextChangeOil = $baseChangeOilKm + $changeOilEvery;
                $remainingKm = $nextChangeOil - (int) $row->new_odometer;

                $lastOdometer = (int) $row->new_odometer;

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
        }

        return view('maintenance.odometer.report', compact(
            'records',
            'buses',
            'busId',
            'month',
            'selectedBus',
            'lastChangeOilKm'
        ));
    }
}
