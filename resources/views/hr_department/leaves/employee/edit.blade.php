@extends('layouts.app')
@section('title', 'Edit Employee Leave')

@section('content')
    <div class="container" data-layout="container">
        <div class="content">

            {{-- HEADER CARD --}}
            <div class="card mb-4">
                <div class="card-body">
                    <h4>Edit Employee Leave</h4>
                    <p class="text-muted">Modify the leave details below.</p>
                </div>
            </div>

            {{-- FORM CARD --}}
            <div class="card p-4">

                <form action="{{ route('employee-leave.employee.update', $leave) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        {{-- Employee --}}
                        <div class="col-md-6">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select" required>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ $emp->id == $leave->employee_id ? 'selected' : '' }}>
                                        {{ $emp->full_name }} ({{ $emp->position?->title ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Leave Type --}}
                        <div class="col-md-6">
                            <label class="form-label">Leave Type</label>
                            <select name="leave_type" class="form-select" required>
                                <option value="Medical Leave" {{ $leave->leave_type == 'Medical Leave' ? 'selected' : '' }}>
                                    Medical Leave</option>
                                <option value="Emergency Leave"
                                    {{ $leave->leave_type == 'Emergency Leave' ? 'selected' : '' }}>Emergency Leave</option>
                                <option value="Vacation Leave"
                                    {{ $leave->leave_type == 'Vacation Leave' ? 'selected' : '' }}>Vacation Leave</option>
                                <option value="Others" {{ $leave->leave_type == 'Others' ? 'selected' : '' }}>Others
                                </option>
                            </select>
                        </div>

                        {{-- Start Date --}}
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $leave->start_date }}"
                                required>
                        </div>

                        {{-- End Date --}}
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $leave->end_date }}"
                                required>
                        </div>

                        {{-- Reason --}}
                        <div class="col-12">
                            <label class="form-label">Reason</label>
                            <textarea name="reason" class="form-control" rows="3">{{ $leave->reason }}</textarea>
                        </div>

                    </div>

                    <div class="mt-4 text-end">
                        <a href="{{ route('employee-leave.employee.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button class="btn btn-primary">Save Changes</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection
