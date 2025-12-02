<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;

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
        // Base counts
        $today = JobOrder::whereDate('created_at', today())->count();
        $pending = JobOrder::where('job_status', 'Pending')->count();
        $inProgress = JobOrder::where('job_status', 'In Progress')->count();
        $completed = JobOrder::where('job_status', 'Completed')->count();

        $base = 10;

        $todayPercent = ($today / $base) * 100;
        $pendingPercent = ($pending / $base) * 100;
        $inProgressPercent = ($inProgress / $base) * 100;
        $completedPercent = ($completed / $base) * 100;

        // REAL WEEKLY DATA
        $weeks = collect(range(0, 5))->map(function ($i) {
            return now()->subWeeks($i);
        })->reverse();

        // Today tickets per week (created_at)
        $todayWeekly = $weeks->map(function ($week) {
            return JobOrder::whereBetween('created_at', [
                $week->copy()->startOfWeek(),
                $week->copy()->endOfWeek(),
            ])->count();
        });

        // Pending weekly
        $pendingWeekly = $weeks->map(function ($week) {
            return JobOrder::where('job_status', 'Pending')
                ->whereBetween('created_at', [
                    $week->copy()->startOfWeek(),
                    $week->copy()->endOfWeek(),
                ])->count();
        });

        // In progress weekly
        $inProgressWeekly = $weeks->map(function ($week) {
            return JobOrder::where('job_status', 'In Progress')
                ->whereBetween('created_at', [
                    $week->copy()->startOfWeek(),
                    $week->copy()->endOfWeek(),
                ])->count();
        });

        // Completed weekly
        $completedWeekly = $weeks->map(function ($week) {
            return JobOrder::where('job_status', 'Completed')
                ->whereBetween('created_at', [
                    $week->copy()->startOfWeek(),
                    $week->copy()->endOfWeek(),
                ])->count();
        });

        return view('dashboard.dashboard_it', compact(
            'today', 'pending', 'inProgress', 'completed',
            'todayPercent', 'pendingPercent', 'inProgressPercent', 'completedPercent',
            'todayWeekly', 'pendingWeekly', 'inProgressWeekly', 'completedWeekly'
        ));
    }
}
