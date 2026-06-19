@extends('layouts.app')
@section('title', 'Payroll Details')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
            @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">{{ $payroll->payroll_number }}</h4>
                            <div class="text-muted small d-flex flex-wrap gap-3">
                                <span><i class="fas fa-calendar me-1 text-primary"></i>{{ $payroll->cutoff_label }}</span>
                                <span><i class="fas fa-calendar-check me-1 text-info"></i>Contribution: {{ $payroll->contribution_label }}</span>
                                <span>{{ optional($payroll->period_start)->format('M d, Y') }} - {{ optional($payroll->period_end)->format('M d, Y') }}</span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('payroll.export.excel', $payroll) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </a>
                            <a href="{{ route('payroll.export.pdf', $payroll) }}" class="btn btn-danger btn-sm">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a>
                            <a href="{{ route('payroll.index') }}" class="btn btn-falcon-default btn-sm">
                                Back
                            </a>
                            @if ($payroll->status !== 'finalized')
                                <form method="POST" action="{{ route('payroll.finalize', $payroll) }}" onsubmit="return confirm('Finalize this payroll?')">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-lock me-1"></i> Finalize
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-xl-2">
                            <div class="border rounded-3 p-3 h-100">
                                <small class="text-muted d-block">Status</small>
                                <span class="badge {{ $payroll->status === 'finalized' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($payroll->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <div class="border rounded-3 p-3 h-100">
                                <small class="text-muted d-block">Employees</small>
                                <h5 class="mb-0">{{ number_format($totals['employees']) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <div class="border rounded-3 p-3 h-100">
                                <small class="text-muted d-block">Gross Pay</small>
                                <h5 class="mb-0">₱ {{ number_format($totals['gross_pay'], 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <div class="border rounded-3 p-3 h-100 bg-info-subtle border-info-subtle">
                                <small class="text-info d-block fw-semibold">Holiday + Rest + OT</small>
                                <h5 class="mb-0 text-info">₱ {{ number_format($totals['holiday_pay'] + $totals['rest_day_pay'] + $totals['overtime_pay'], 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <div class="border rounded-3 p-3 h-100 bg-danger-subtle border-danger-subtle">
                                <small class="text-danger d-block fw-semibold">All Deductions</small>
                                <h5 class="mb-0 text-danger">₱ {{ number_format($totals['total_employee_government_deductions'] + $totals['other_deductions'], 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <div class="border rounded-3 p-3 h-100 bg-success-subtle border-success-subtle">
                                <small class="text-success d-block fw-semibold">Net Pay</small>
                                <h4 class="mb-0 text-success">₱ {{ number_format($totals['net_pay'], 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <h5 class="mb-0">Employee Payroll Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-200 text-900">
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-end">Payable</th>
                                    <th class="text-end">Regular</th>
                                    <th class="text-end">Holiday/Rest/OT</th>
                                    <th class="text-end">Gov.</th>
                                    <th class="text-end">Loans/Other</th>
                                    <th class="text-end">Net</th>
                                    <th width="80"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payroll->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item->employee_name }}</div>
                                            <small class="text-muted">
                                                {{ $item->employee_no ?: 'No Employee No' }}
                                                @if ($item->biometric_employee_id)
                                                    | Bio: {{ $item->biometric_employee_id }}
                                                @endif
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <div>{{ number_format($item->total_payable_days, 2) }} day</div>
                                            <small class="text-muted">{{ number_format($item->total_payable_hours, 2) }} hr</small>
                                        </td>
                                        <td class="text-end">₱ {{ number_format($item->regular_pay, 2) }}</td>
                                        <td class="text-end">
                                            <div>₱ {{ number_format($item->holiday_pay + $item->rest_day_pay + $item->overtime_pay, 2) }}</div>
                                            <small class="text-muted">
                                                H: {{ number_format($item->holiday_pay, 2) }} |
                                                R: {{ number_format($item->rest_day_pay, 2) }} |
                                                OT: {{ number_format($item->overtime_pay, 2) }}
                                            </small>
                                        </td>
                                        <td class="text-end text-danger">
                                            ₱ {{ number_format($item->total_employee_government_deductions, 2) }}
                                            <div class="small text-muted">
                                                SSS {{ number_format($item->sss_employee, 2) }} / PH {{ number_format($item->philhealth_employee, 2) }} / PI {{ number_format($item->pagibig_employee, 2) }}
                                            </div>
                                        </td>
                                        <td class="text-end text-danger">₱ {{ number_format($item->other_deductions, 2) }}</td>
                                        <td class="text-end fw-bold text-success">₱ {{ number_format($item->net_pay, 2) }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('payroll.items.show', [$payroll, $item]) }}" class="btn btn-falcon-primary btn-sm">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-5">No payroll items found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($payroll->items->isNotEmpty())
                                <tfoot class="bg-100 fw-bold">
                                    <tr>
                                        <td>TOTAL</td>
                                        <td></td>
                                        <td class="text-end">₱ {{ number_format($totals['regular_pay'], 2) }}</td>
                                        <td class="text-end">₱ {{ number_format($totals['holiday_pay'] + $totals['rest_day_pay'] + $totals['overtime_pay'], 2) }}</td>
                                        <td class="text-end">₱ {{ number_format($totals['total_employee_government_deductions'], 2) }}</td>
                                        <td class="text-end">₱ {{ number_format($totals['other_deductions'], 2) }}</td>
                                        <td class="text-end text-success">₱ {{ number_format($totals['net_pay'], 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
