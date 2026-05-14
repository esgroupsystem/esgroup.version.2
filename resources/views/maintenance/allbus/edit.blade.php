@extends('layouts.app')
@section('title', 'Edit Bus')

@section('content')
    <div class="container">
        <div class="content">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <span class="fas fa-edit text-primary me-2"></span>
                        Edit Bus
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('allbus.update', $bus->id) }}" method="POST">
                        @method('PUT')
                        @include('maintenance.allbus._form', ['bus' => $bus])
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
