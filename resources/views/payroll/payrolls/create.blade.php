@extends('layouts.app')
@section('title', 'Generate Payroll')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <h4 class="mb-1 text-dark">
                        <i class="fas fa-calculator text-primary me-2"></i>
                        Generate Payroll
                    </h4>
                    <p class="mb-0 text-muted small">
                        Uses 9 hours = 1 day, holiday before/after rule, monthly-cycle government deductions, and payment logs.
                    </p>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('payroll.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Cutoff Month</label>
                                <select name="cutoff_month" class="form-select" required>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected((int) old('cutoff_month', $defaultCutoffMonth) === $m)>
                                            {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Cutoff Year</label>
                                <input type="number" name="cutoff_year" class="form-control" value="{{ old('cutoff_year', $defaultCutoffYear) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Cutoff Type</label>
                                <select name="cutoff_type" class="form-select" required>
                                    <option value="first" @selected(old('cutoff_type', $defaultCutoffType) === 'first')>
                                        1st Cutoff (11–25)
                                    </option>
                                    <option value="second" @selected(old('cutoff_type', $defaultCutoffType) === 'second')>
                                        2nd Cutoff (26–10 next month)
                                    </option>
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="alert alert-info border-0 mb-0">
                                    <strong>Payroll basis:</strong>
                                    A 2nd cutoff ending on Feb 10 plus 1st cutoff Feb 11–25 belongs to the February contribution month.
                                    Government contribution schedules are configurable in <code>config/payroll.php</code>.
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="rebuild_summary" value="1" id="rebuild_summary" @checked(old('rebuild_summary', '1'))>
                                    <label class="form-check-label" for="rebuild_summary">
                                        Rebuild daily attendance summary before generating payroll
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" rows="3" class="form-control" placeholder="Optional payroll notes">{{ old('remarks') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-play me-1"></i> Generate Payroll
                            </button>
                            <a href="{{ route('payroll.index') }}" class="btn btn-falcon-default">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
