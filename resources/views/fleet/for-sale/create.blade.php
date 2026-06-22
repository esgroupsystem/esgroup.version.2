@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h3 class="mb-1">Add For Sale Unit</h3>
                <p class="text-muted mb-0">
                    Add a new unit to the For Sale monitoring database.
                </p>
            </div>

            <a href="{{ route('fleet.for-sale-units.index') }}" class="btn btn-falcon-default">
                Back to List
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @include('fleet.for-sale._form', [
                    'action' => route('fleet.for-sale-units.store'),
                    'method' => 'POST',
                    'buttonLabel' => 'Save Unit',
                ])
            </div>
        </div>
    </div>
@endsection
