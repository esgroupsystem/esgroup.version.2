@extends('layouts.app')
@section('title', 'Edit Conductor Leave')

@section('content')
<div class="container py-4">

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="mb-3">Edit Leave</h4>

            <form action="{{ route('conductor-leave.conductor.update', $leave->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Conductor --}}
                <div class="mb-3">
                    <label class="form-label">Conductor</label>
                    <select name="employee_id" class="form-select" required>
                        @foreach ($conductors as $conductor)
                            <option value="{{ $conductor->id }}" {{ $conductor->id == $leave->employee_id ? 'selected':'' }}>
                                {{ $conductor->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Leave Type --}}
                <div class="mb-3">
                    <label class="form-label">Leave Type</label>
                    <input type="text" name="leave_type" class="form-control"
                        value="{{ $leave->leave_type }}" required>
                </div>

                {{-- Start Date --}}
                <div class="mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control"
                        value="{{ $leave->start_date }}" required>
                </div>

                {{-- End Date --}}
                <div class="mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control"
                        value="{{ $leave->end_date }}" required>
                </div>

                {{-- Reason --}}
                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <textarea name="reason" rows="4" class="form-control">{{ $leave->reason }}</textarea>
                </div>

                <button class="btn btn-primary">Save Changes</button>
                <a href="{{ route('conductor-leave.conductor.index') }}" class="btn btn-secondary">Cancel</a>
            </form>

        </div>
    </div>

</div>
@endsection
