@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        {{-- PAGE HEADER --}}
        <div class="card mb-3 border-0 shadow-sm overflow-hidden">
            <div class="card-body bg-body-tertiary">
                <div class="row align-items-center g-3">
                    <div class="col">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2 fs-10">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('fleet.for-sale-units.index') }}">For Sale Units</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Update Unit</li>
                            </ol>
                        </nav>

                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center"
                                style="width: 46px; height: 46px;">
                                <span class="fas fa-edit fs-6"></span>
                            </div>

                            <div>
                                <h3 class="mb-1 fw-bold">Update For Sale Unit</h3>
                                <p class="text-muted mb-0">
                                    Update unit monitoring details and sync changes to the main bus list.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-auto">
                        <a href="{{ route('fleet.for-sale-units.index') }}" class="btn btn-falcon-default">
                            <span class="fas fa-arrow-left me-1"></span>
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- SUCCESS MESSAGE --}}
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <span class="fas fa-check-circle me-2"></span>
                    <div>{{ session('success') }}</div>
                </div>
            </div>
        @endif

        {{-- VALIDATION SUMMARY --}}
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                <div class="d-flex">
                    <span class="fas fa-exclamation-circle me-2 mt-1"></span>
                    <div>
                        <h6 class="alert-heading mb-1">Please check the form</h6>
                        <p class="mb-0">Some required or invalid fields need correction before updating.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- FORM CARD --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 fw-semibold">
                            <span class="fas fa-clipboard-check text-warning me-2"></span>
                            Unit Monitoring Details
                        </h5>
                        <small class="text-muted">
                            Review and update the current unit record.
                        </small>
                    </div>

                    <span class="badge badge-soft-warning text-warning">
                        Editing Record
                    </span>
                </div>
            </div>

            <div class="card-body">
                @include('fleet.for-sale._form', [
                    'action' => route('fleet.for-sale-units.update', $forSaleRecord),
                    'method' => 'PUT',
                    'buttonLabel' => 'Update Unit',
                ])
            </div>
        </div>
    </div>
@endsection
