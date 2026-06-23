@extends('layouts.app')

@section('title', '404 Not Found')

@section('content')
    @include('errors.partials.error-popup', [
        'code' => '404',
        'title' => 'Page Not Found',
        'message' =>
            'The page or module you are trying to open does not exist. Click okay to return to the dashboard.',
        'icon' => 'fas fa-search',
        'tone' => 'warning',
        'details' => [
            'The URL may be incorrect.',
            'The module may have been moved or deleted.',
            'The route may not be registered yet.',
        ],
    ])
@endsection
