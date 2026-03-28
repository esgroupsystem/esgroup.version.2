@extends('layouts.app')
@section('title', 'Generate Payroll')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Generate Payroll</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('payroll.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Cutoff Month</label>
                                <select name="cutoff_month" class="form-select" required>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}"
                                            {{ (int) old('cutoff_month', $defaultCutoffMonth) === $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                                @error('cutoff_month')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Cutoff Year</label>
                                <input type="number" name="cutoff_year" class="form-control"
                                    value="{{ old('cutoff_year', $defaultCutoffYear) }}" required>
                                @error('cutoff_year')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Cutoff Type</label>
                                <select name="cutoff_type" class="form-select" required>
                                    <option value="first"
                                        {{ old('cutoff_type', $defaultCutoffType) === 'first' ? 'selected' : '' }}>
                                        1st Cutoff (11–25)
                                    </option>
                                    <option value="second"
                                        {{ old('cutoff_type', $defaultCutoffType) === 'second' ? 'selected' : '' }}>
                                        2nd Cutoff (26–10 next month)
                                    </option>
                                </select>
                                @error('cutoff_type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="rebuild_summary" value="1"
                                        id="rebuild_summary" {{ old('rebuild_summary', '1') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="rebuild_summary">
                                        Rebuild daily attendance summary before generating payroll
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" rows="3" class="form-control">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                Generate Payroll
                            </button>
                            <a href="{{ route('payroll.index') }}" class="btn btn-light">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
