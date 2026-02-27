@extends('layouts.app')
@section('title', 'Employees Management')

@section('content')
<div class="container" data-layout="container">

    {{-- Optional: keep this here, or better move to layouts.app --}}
    @include('hr_department.employees.scripts._fluid_container')

    <div class="content">

        {{-- Header Card --}}
        @include('hr_department.employees.partials._header')

        {{-- Employee List Card --}}
        <div class="card mb-4">
            <div class="card-header pb-0">
                <h5 class="mb-0">Employee List</h5>
            </div>

            {{-- Search --}}
            @include('hr_department.employees.partials._search')

            {{-- Table --}}
            @include('hr_department.employees.partials._table_wrapper')
        </div>

    </div>
</div>

{{-- Add Employee Modal --}}
@include('hr_department.employees.modals._add_employee')

@endsection

@push('scripts')
    @include('hr_department.employees.scripts._index', ['departments' => $departments])
@endpush

@push('styles')
    @include('hr_department.employees.styles._pagination')
@endpush