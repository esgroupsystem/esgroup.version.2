@extends('layouts.app')
@section('title', 'Payroll Batches')

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
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">
                                <i class="fas fa-money-check-alt text-primary me-2"></i>
                                Payroll Batches
                            </h4>
                            <p class="mb-0 text-muted small">
                                Overall payroll computation: attendance hours, holidays, absences, adjustments, government deductions, and loan logs.
                            </p>
                        </div>
                        <a href="{{ route('payroll.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Generate Payroll
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('payroll.index') }}" class="row g-2 align-items-end mb-3">
                        <div class="col-lg-4">
                            <label class="form-label small text-muted">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Payroll no., status, remarks" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label small text-muted">Cutoff</label>
                            <select name="cutoff_type" class="form-select">
                                <option value="">All Cutoffs</option>
                                <option value="first" @selected(request('cutoff_type') === 'first')>1st Cutoff</option>
                                <option value="second" @selected(request('cutoff_type') === 'second')>2nd Cutoff</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label small text-muted">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                                <option value="finalized" @selected(request('status') === 'finalized')>Finalized</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button class="btn btn-falcon-default" type="submit">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive scrollbar">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-200 text-900">
                                <tr>
                                    <th>Payroll</th>
                                    <th>Coverage</th>
                                    <th>Contribution Month</th>
                                    <th class="text-center">Employees</th>
                                    <th>Status</th>
                                    <th>Generated</th>
                                    <th class="text-end" width="190">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payrolls as $payroll)
                                    <tr>
                                        <td>
                                            <a href="{{ route('payroll.show', $payroll) }}" class="fw-bold text-decoration-none">
                                                {{ $payroll->payroll_number }}
                                            </a>
                                            <div class="small text-muted">{{ $payroll->cutoff_label }}</div>
                                        </td>
                                        <td class="text-nowrap">
                                            {{ optional($payroll->period_start)->format('M d, Y') }} -
                                            {{ optional($payroll->period_end)->format('M d, Y') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                {{ $payroll->contribution_label }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-semibold">{{ $payroll->items_count }}</td>
                                        <td>
                                            <span class="badge {{ $payroll->status === 'finalized' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ ucfirst($payroll->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>{{ $payroll->generator->name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ optional($payroll->generated_at)->format('M d, Y h:i A') }}</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('payroll.show', $payroll) }}" class="btn btn-falcon-primary">View</a>
                                                @if ($payroll->status !== 'finalized')
                                                    <form method="POST" action="{{ route('payroll.destroy', $payroll) }}" onsubmit="return confirm('Delete this draft payroll?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-falcon-danger rounded-start-0">Delete</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="fas fa-folder-open fa-2x d-block mb-2"></i>
                                            No payroll records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $payrolls->links('pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
