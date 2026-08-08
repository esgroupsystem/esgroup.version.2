<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\BenefitsRecordIndexRequest;
use App\Services\Payroll\BenefitRecordsService;
use Illuminate\View\View;

class BenefitsRecordController extends Controller
{
    public function __construct(
        private readonly BenefitRecordsService $benefitRecordsService
    ) {}

    public function index(BenefitsRecordIndexRequest $request): View
    {
        $filters = $request->validated();

        $data = $this->benefitRecordsService->buildIndex(
            $filters,
            session('payroll_allowed_groups')
        );

        return view('payroll.benefits_records.index', [
            ...$data,
            'filters' => $filters,
        ]);
    }

    public function overall(BenefitsRecordIndexRequest $request): View
    {
        $filters = $request->validated();

        $data = $this->benefitRecordsService->buildOverall(
            $filters,
            session('payroll_allowed_groups')
        );

        return view('payroll.benefits_records.overall', [
            ...$data,
            'filters' => $filters,
        ]);
    }

    public function print(BenefitsRecordIndexRequest $request): View
    {
        $filters = $request->validated();

        $data = $this->benefitRecordsService->buildOverall(
            $filters,
            session('payroll_allowed_groups')
        );

        return view('payroll.benefits_records.print', [
            ...$data,
            'filters' => $filters,
        ]);
    }
}
