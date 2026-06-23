@extends('layouts.app')

@section('title', '429 Too Many Requests')

@section('content')
    @include('errors.partials.error-popup', [
        'code' => '429',
        'title' => 'Too Many Requests',
        'message' =>
            'The system detected too many requests in a short time. Click okay to return to the dashboard.',
        'icon' => 'fas fa-exclamation-triangle',
        'tone' => 'warning',
        'details' => [
            'Too many attempts were made too quickly.',
            'The system temporarily blocked repeated requests.',
            'Wait a moment before trying the same action again.',
        ],
    ])
@endsection
