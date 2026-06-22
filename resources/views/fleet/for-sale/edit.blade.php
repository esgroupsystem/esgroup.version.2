@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h3 class="mb-1">Update For Sale Unit</h3>
                <p class="text-muted mb-0">
                    Update unit monitoring details and sync changes to the main bus list.
                </p>
            </div>

            <a href="{{ route('fleet.for-sale-units.index') }}" class="btn btn-falcon-default">
                Back to List
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <span class="fas fa-check-circle me-1"></span>
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
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
