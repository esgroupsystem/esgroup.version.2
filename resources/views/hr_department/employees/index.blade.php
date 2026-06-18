@extends('layouts.app')

@section('title', 'Employees Management')

@section('content')
    {{-- 
        IMPORTANT:
        Do not add another <div class="container" data-layout="container"> here
        if layouts.app already contains the Falcon container/sidebar/topbar structure.
    --}}

    <div class="content">

        {{-- Header Card / Employee Summary --}}
        @include('hr_department.employees.partials._header')

        {{-- Employee Directory Card --}}
        <div class="card border-0 shadow-sm mb-4 employee-directory-card">

            <div class="card-header bg-body-tertiary border-bottom py-3">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">

                    <div>
                        <h5 class="mb-1 text-900">
                            <span class="fas fa-users text-primary me-2"></span>
                            Employee List
                        </h5>

                        <p class="mb-0 fs--1 text-600">
                            Search, filter, view, and manage employee records in one directory.
                        </p>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('employees.staff.index') }}" class="btn btn-sm btn-falcon-default">
                            <span class="fas fa-sync-alt me-1"></span>
                            Refresh
                        </a>

                        <button type="button" class="btn btn-sm btn-falcon-primary" data-bs-toggle="modal"
                            data-bs-target="#addEmployeeModal">
                            <span class="fas fa-plus me-1"></span>
                            Add Employee
                        </button>
                    </div>

                </div>
            </div>

            {{-- Search / Filters --}}
            @include('hr_department.employees.partials._search')

            {{-- Employee Table --}}
            @include('hr_department.employees.partials._table_wrapper')

        </div>

    </div>

    {{-- Add Employee Modal --}}
    @include('hr_department.employees.modals._add_employee')
@endsection

@push('scripts')
    @include('hr_department.employees.scripts._index', [
        'departments' => $departments ?? collect(),
    ])
@endpush

@push('styles')
    @include('hr_department.employees.styles._pagination')
@endpush
