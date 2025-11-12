<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.dashboard');
    }

    public function analyticsindex()
    {
        return view('dashboard.analytics');
    }

    public function crmindex()
    {
        return view('dashboard.crm');
    }

    public function itindex()
    {
        // Count Job Orders
        $pending = JobOrder::where('job_status', 'Pending')->count();
        $inProcess = JobOrder::where('job_status', 'In Progress')->count();
        $due = JobOrder::whereIn('job_status', ['Pending', 'In Progress'])
            ->whereDate('job_date_filled', '<', Carbon::now()->subDays(2))
            ->count();
        $unassigned = JobOrder::whereNull('job_assign_person')->count();

        // Percent values (static or computed)
        $percentPending = '+5%';
        $percentInProcess = '+3%';
        $percentDue = '-2%';
        $percentUnassigned = '+1%';

        // Get past 7 days for chart
        $days = collect(range(6, 0))
            ->map(fn ($i) => Carbon::now()->subDays($i)->format('M d'))
            ->values();

        // Chart data initialization
        $chartData = [
            'Pending' => [],
            'In Process' => [],
            'Due' => [],
            'Unassigned' => [],
        ];

        // Fill chart data
        foreach ($days as $day) {
            $date = Carbon::createFromFormat('M d', $day)->year(Carbon::now()->year);

            $chartData['Pending'][] = JobOrder::where('job_status', 'Pending')
                ->whereDate('job_date_filled', $date)->count();

            $chartData['In Process'][] = JobOrder::where('job_status', 'In Progress')
                ->whereDate('job_date_filled', $date)->count();

            $chartData['Due'][] = JobOrder::whereIn('job_status', ['Pending', 'In Progress'])
                ->whereDate('job_date_filled', '<', $date->copy()->subDays(2))->count();

            $chartData['Unassigned'][] = JobOrder::whereNull('job_assign_person')
                ->whereDate('job_date_filled', $date)->count();
        }

        return view('dashboard.dashboard_it', compact(
            'pending', 'inProcess', 'due', 'unassigned',
            'percentPending', 'percentInProcess', 'percentDue', 'percentUnassigned',
            'days', 'chartData'
        ));
    }
}
