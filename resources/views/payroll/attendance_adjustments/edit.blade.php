@extends('layouts.app')
@section('title', 'Edit Payroll Attendance Adjustment')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Edit Payroll Attendance Adjustment</h5>
                </div>

                <div class="card-body">
                    <form method="POST"
                        action="{{ route('payroll-attendance-adjustments.update', $payrollAttendanceAdjustment) }}">
                        @csrf
                        @method('PUT')
                        @include('payroll.attendance_adjustments._form')

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Adjustment</button>
                            <a href="{{ route('payroll-attendance-adjustments.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
