@extends('layouts.app')

@section('title', '500 Internal Server Error')

@section('content')
    @include('errors.partials.error-popup', [
        'code' => '500',
        'title' => 'Internal Server Error',
        'message' =>
            'Something unexpected happened while processing your request. Click okay to return to the dashboard.',
        'icon' => 'fas fa-bug',
        'tone' => 'danger',
        'details' => [
            'A server-side error occurred.',
            'The action was not completed successfully.',
            'Check the Laravel log if this error persists.',
        ],
    ])
@endsection
