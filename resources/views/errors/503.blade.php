@extends('layouts.app')

@section('title', '503 Service Unavailable')

@section('content')
    @include('errors.partials.error-popup', [
        'code' => '503',
        'title' => 'Service Temporarily Unavailable',
        'message' =>
            'The system is currently unavailable or under maintenance. Click okay to return to the dashboard.',
        'icon' => 'fas fa-tools',
        'tone' => 'secondary',
        'details' => [
            'Maintenance mode may be enabled.',
            'The server may be temporarily busy.',
            'Try again after a few minutes.',
        ],
    ])
@endsection
