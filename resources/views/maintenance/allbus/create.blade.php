@extends('layouts.app')
@section('title', 'Add Bus')

@section('content')
    <div class="container">
        <div class="content">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <span class="fas fa-plus text-primary me-2"></span>
                        Add Bus
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('allbus.store') }}" method="POST">
                        @include('maintenance.allbus._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
