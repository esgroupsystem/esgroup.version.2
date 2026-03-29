@extends('layouts.app')

@section('title', 'Edit Holiday')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body-tertiary border-bottom">
                    <h4 class="mb-0">Edit Holiday</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('holidays.update', $holiday) }}">
                        @csrf
                        @method('PUT')
                        @include('payroll.holidays._form')
                        <div class="mt-4">
                            <button class="btn btn-primary">Update Holiday</button>
                            <a href="{{ route('holidays.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
