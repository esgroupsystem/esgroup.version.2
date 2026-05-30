@extends('layouts.app')
@section('title', 'Create Conductor Leave')

@section('content')
    <div class="container" data-layout="container">
        <div class="content">

            <div class="card mb-4">
                <div class="card-body">
                    <h4>Create Conductor Leave</h4>
                    <p class="text-muted">Fill out the details below to record the leave request.</p>
                </div>
            </div>

            <div class="card p-4">

                <form action="{{ route('conductor-leave.conductor.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">
                                Conductor <span class="text-danger">*</span>
                            </label>

                            <select name="employee_id" id="employee_id" class="form-select" required>

                                <option value="">Select Conductor</option>

                                @foreach ($conductors as $c)
                                    <option value="{{ $c->id }}"
                                        {{ old('employee_id') == $c->id ? 'selected' : '' }}>

                                        {{ $c->full_name }}

                                        @if ($c->position)
                                            | {{ $c->position->title }}
                                        @endif

                                    </option>
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
                        <a href="{{ route('conductor-leave.conductor.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button class="btn btn-primary">Save Leave</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.addEventListener('load', function() {

            if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
                console.error('Select2 not loaded');
                return;
            }

            const employeeSelect = $('#employee_id');

            if (employeeSelect.hasClass('select2-hidden-accessible')) {
                employeeSelect.select2('destroy');
            }

            employeeSelect.select2({
                width: '100%',
                placeholder: 'Search Conductor...',
                allowClear: true,

                matcher: function(params, data) {

                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    if (!data.text) {
                        return null;
                    }

                    const term = params.term
                        .toLowerCase()
                        .replace(/[^a-z0-9]/g, '');

                    const text = data.text
                        .toLowerCase()
                        .replace(/[^a-z0-9]/g, '');

                    return text.includes(term) ? data : null;
                }
            });

        });
    </script>
@endpush
