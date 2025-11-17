@extends('layouts.app')
@section('title', 'Create Driver Leave')

@section('content')
<div class="container" data-layout="container">
    <div class="content">

        <div class="card mb-4">
            <div class="card-body">
                <h4>Create Driver Leave</h4>
                <p class="text-muted">Fill out the details below to record the leave request.</p>
            </div>
        </div>

        <div class="card p-4">

            <form action="{{ route('driver-leave.driver.store') }}" method="POST">
                @csrf

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Driver</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Driver</option>
                            @foreach ($drivers as $d)
                                <option value="{{ $d->id }}">{{ $d->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Leave Type</label>
                        <select name="leave_type" class="form-select" required>
                            <option value="Medical Leave">Medical Leave</option>
                            <option value="Emergency Leave">Emergency Leave</option>
                            <option value="Vacation Leave">Vacation Leave</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3"></textarea>
                    </div>

                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('driver-leave.driver.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button class="btn btn-primary">Save Leave</button>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection
