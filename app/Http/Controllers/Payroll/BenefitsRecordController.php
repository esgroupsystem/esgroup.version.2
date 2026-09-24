<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\BenefitsRecordIndexRequest;
use App\Services\Payroll\BenefitRecordsService;
use Carbon\Carbon;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class BenefitsRecordController extends Controller
{
    public function __construct(
        private readonly BenefitRecordsService $benefitRecordsService
    ) {}

    public function index(BenefitsRecordIndexRequest $request): Response
    {
        $filters = $request->validated();

        $data = $this->benefitRecordsService->buildIndex(
            $filters,
            session('payroll_allowed_groups')
        );

        $totals = $data['totals'];

        return Inertia::render('payroll/benefits-records/index', [
            'employees' => $data['employees']->through(fn (array $row): array => [
                'id' => $row['employee']->id,
                'name' => $row['employee']->payroll_display_name,
                'employee_no' => $row['employee']->effective_employee_no,
                'company' => $row['employee']->company?->name,
                'group_label' => $row['employee']->payroll_group_label,
                'posted' => (bool) $row['summary']['posted'],
                'settlement_status' => (string) ($row['summary']['settlement_status'] ?? 'not_posted'),
                'settlement_mode' => (string) data_get($row['summary'], 'settlement_meta.mode', ''),
                'payroll_numbers' => $row['summary']['payroll_numbers'],
                'programs' => [
                    $this->program('SSS', $row['identifiers']['sss'] ?? null, $row['summary'], 'sss_employee_total', 'sss_employee_collected', 'sss_employer_total', 'sss_total_contribution'),
                    $this->program('PhilHealth', $row['identifiers']['philhealth'] ?? null, $row['summary'], 'philhealth_employee', 'philhealth_employee_collected', 'philhealth_employer', 'philhealth_total'),
                    $this->program('Pag-IBIG', $row['identifiers']['pagibig'] ?? null, $row['summary'], 'pagibig_employee', 'pagibig_employee_collected', 'pagibig_employer', 'pagibig_total'),
                ],
                'employee_total' => (float) $row['summary']['employee_total'],
                'employee_collected_total' => (float) $row['summary']['employee_collected_total'],
                'employer_total' => (float) $row['summary']['employer_total'],
                'grand_total' => (float) $row['summary']['grand_total'],
                'unrecovered' => (float) $row['summary']['employee_share_unrecovered'],
            ]),
            'kpis' => [
                'active' => (int) $data['activeEmployeeCount'],
                'posted' => (int) $data['postedEmployeeCount'],
                'not_posted' => (int) $data['notPostedEmployeeCount'],
                'employee_due' => (float) ($totals->employee_total ?? 0),
                'collected' => (float) ($totals->sss_employee_collected ?? 0) + (float) ($totals->philhealth_employee_collected ?? 0) + (float) ($totals->pagibig_employee_collected ?? 0),
                'unrecovered' => (float) ($totals->employee_share_unrecovered ?? 0),
                'employer' => (float) ($totals->employer_total ?? 0),
            ],
            ...$this->sharedProps($filters, $data['groupOptions']),
        ]);
    }

    public function overall(BenefitsRecordIndexRequest $request): Response
    {
        $filters = $request->validated();

        $data = $this->benefitRecordsService->buildOverall(
            $filters,
            session('payroll_allowed_groups')
        );

        return Inertia::render('payroll/benefits-records/overall', [
            'rows' => collect($data['rows'])
                ->filter(fn (array $row): bool => (bool) $row['summary']['posted'])
                ->map(fn (array $row): array => [
                    'id' => $row['employee']->id,
                    'name' => $row['employee']->payroll_display_name,
                    'company' => $row['company_name'],
                    ...collect($row['summary'])->only([
                        'sss_employee_regular_ss', 'sss_employer_regular_ss', 'sss_employer_ec',
                        'sss_employee_mpf', 'sss_employer_mpf', 'sss_total_contribution',
                        'philhealth_employee', 'philhealth_employer', 'philhealth_total',
                        'pagibig_employee', 'pagibig_employer', 'pagibig_total',
                    ])->map(fn ($value): float => (float) $value)->all(),
                ])
                ->values(),
            'totals' => $data['totals'],
            'companyTotals' => $data['companyTotals'],
            'payrollNumbers' => $data['payrollNumbers'],
            ...$this->sharedProps($filters, $data['groupOptions']),
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

    private function program(string $name, ?string $id, array $summary, string $due, string $collected, string $employer, string $total): array
    {
        return [
            'name' => $name,
            'id' => $id,
            'due' => (float) ($summary[$due] ?? 0),
            'collected' => (float) ($summary[$collected] ?? 0),
            'employer' => (float) ($summary[$employer] ?? 0),
            'total' => (float) ($summary[$total] ?? 0),
        ];
    }

    private function sharedProps(array $filters, $groupOptions): array
    {
        $month = (int) ($filters['month'] ?? now('Asia/Manila')->month);
        $year = (int) ($filters['year'] ?? now('Asia/Manila')->year);
        $query = array_filter([
            'month' => $month,
            'year' => $year,
            'search' => $filters['search'] ?? null,
            'garage_group' => $filters['garage_group'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        return [
            'filters' => [
                'month' => $month,
                'year' => $year,
                'search' => (string) ($filters['search'] ?? ''),
                'garage_group' => isset($filters['garage_group']) ? (string) $filters['garage_group'] : '',
            ],
            'periodLabel' => Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila')->format('F Y'),
            'groupOptions' => collect($groupOptions)->mapWithKeys(fn ($label, $id): array => [(string) $id => (string) $label]),
            'urls' => [
                'index' => route('benefits-records.index'),
                'overall' => route('benefits-records.overall'),
                'indexWithFilters' => route('benefits-records.index', $query),
                'overallWithFilters' => route('benefits-records.overall', $query),
                'print' => route('benefits-records.print', $query),
            ],
        ];
    }
}
