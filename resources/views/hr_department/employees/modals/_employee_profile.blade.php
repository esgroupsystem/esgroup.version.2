@extends('layouts.app')
@section('title', ($employee->full_name ?? 'Employee') . ' | Employee 201')

@php
    $profilePath = $employee->asset?->profile_picture
        ? asset('storage/' . $employee->asset->profile_picture)
        : asset('assets/img/no-image-default.png');
@endphp

@section('content')
<div class="container" data-layout="container">

    {{-- Header / Hero --}}
    @include('hr_department.employees.partials._profile_header', compact('employee', 'profilePath'))

    <div class="row g-3">
        {{-- LEFT SIDE --}}
        <div class="col-lg-8">

            {{-- 201 View Card --}}
            @include('hr_department.employees.partials._card_201_view', compact('employee'))

            {{-- Status Details Card --}}
            @include('hr_department.employees.partials._card_status_details', compact('employee'))

            {{-- History Timeline Card --}}
            @include('hr_department.employees.partials._card_history', compact('employee'))

            {{-- Logs Card --}}
            @include('hr_department.employees.partials._card_logs', compact('employee', 'logs', 'deptMap', 'posMap'))

        </div>

        {{-- RIGHT SIDE --}}
        <div class="col-lg-4">

            {{-- Attachments Card --}}
            @include('hr_department.employees.partials._sidebar_attachments', compact('employee'))

            {{-- Employee Info Card --}}
            @include('hr_department.employees.partials._sidebar_info', compact('employee', 'tenure', 'age'))

        </div>
    </div>
</div>

{{-- MODALS --}}
@include('hr_department.employees.modals._add_history', compact('employee'))
@include('hr_department.employees.modals._upload_attachment', compact('employee'))
@include('hr_department.employees.modals._edit_status_details', compact('employee'))
@include('hr_department.employees.modals._edit_201', compact('employee'))
@include('hr_department.employees.modals._edit_profile', compact('employee', 'departments', 'profilePath'))
@include('hr_department.employees.modals._cropper')

@endsection

@push('scripts')
    @include('hr_department.employees.scripts._employee_profile')
@endpush

@push('styles')
    @include('hr_department.employees.styles._employee_profile')
@endpush