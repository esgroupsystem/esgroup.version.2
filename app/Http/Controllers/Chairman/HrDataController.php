<?php

namespace App\Http\Controllers\Chairman;

use App\Http\Controllers\Controller;
use App\Services\Reports\HrDataReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrDataController extends Controller
{
    public function __construct(
        private readonly HrDataReportService $hrDataReportService
    ) {}

    public function index(Request $request): View
    {
        $year = (int) $request->integer('year', now()->year);

        return view('chairman.hr.index', $this->hrDataReportService->getDashboardData($year));
    }
}
