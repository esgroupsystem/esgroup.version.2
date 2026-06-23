@extends('layouts.app')

@section('title', '419 Session Expired')

@section('content')
    @include('errors.partials.error-popup', [
        'code' => '419',
        'title' => 'Session Expired',
        'message' =>
            'Your secure session expired because of inactivity or token mismatch. Click okay to return to the dashboard.',
        'icon' => 'fas fa-clock',
        'tone' => 'info',
        'details' => [
            'The page was idle for too long.',
            'The CSRF token expired.',
            'Refreshing the form before submitting may be required.',
        ],
    ])
@endsection
