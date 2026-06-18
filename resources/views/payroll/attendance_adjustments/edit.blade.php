@extends('layouts.app')
@section('title', 'Edit Payroll Attendance Adjustment')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">
                                <span class="fas fa-edit text-warning me-2"></span>
                                Edit Payroll Attendance Adjustment
                            </h5>
                            <p class="mb-0 fs-10 text-600">
                                Update the approved adjustment record.
                            </p>
                        </div>

                        <a href="{{ route('payroll-attendance-adjustments.index') }}" class="btn btn-falcon-default btn-sm">
                            <span class="fas fa-arrow-left me-1"></span>
                            Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST"
                        action="{{ route('payroll-attendance-adjustments.update', $payrollAttendanceAdjustment) }}">
                        @csrf
                        @method('PUT')

                        @include('payroll.attendance_adjustments._form')

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('payroll-attendance-adjustments.index') }}" class="btn btn-light">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <span class="fas fa-save me-1"></span>
                                Update Adjustment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
