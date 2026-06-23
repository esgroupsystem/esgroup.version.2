@extends('layouts.app')

@section('title', '403 Forbidden')

@section('content')
    @include('errors.partials.error-popup', [
        'code' => '403',
        'title' => 'Access Forbidden',
        'message' =>
            'Your account does not have permission to open this module. Click okay to return to the dashboard.',
        'icon' => 'fas fa-ban',
        'tone' => 'danger',
        'details' => [
            'Your role permissions may be restricted.',
            'The page may belong to another department or access level.',
            'Your account permission may have changed.',
        ],
    ])
@endsection
