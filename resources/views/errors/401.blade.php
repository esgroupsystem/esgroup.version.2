@extends('layouts.landing')

@section('title', '401 Unauthorized')

@section('content')
    @include('errors.partials.error-popup', [
        'code' => '401',
        'title' => 'Unauthorized Access',
        'message' => 'Your session is not authorized to continue. Click okay to return to the dashboard.',
        'icon' => 'fas fa-lock',
        'tone' => 'primary',
        'details' => [
            'You may not be logged in.',
            'Your session may have expired.',
            'The requested module requires authentication.',
        ],
    ])
@endsection
