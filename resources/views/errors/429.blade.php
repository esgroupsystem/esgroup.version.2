@extends('layouts.landing')

@section('title', '429 Too Many Requests')

@section('content')
    <main class="main" id="top">
        <div class="container-fluid bg-100 min-vh-100 d-flex align-items-center justify-content-center">

            <div class="card shadow-sm border-0 text-center p-5" style="max-width: 520px; width:100%;">

                <div class="mb-4">
                    <div class="avatar avatar-5xl mx-auto">
                        <div class="avatar-name rounded-circle bg-soft-warning text-warning">
                            <span class="fas fa-exclamation-triangle fs-1"></span>
                        </div>
                    </div>
                </div>

                <h1 class="display-4 fw-bold text-warning mb-2">
                    429
                </h1>

                <h4 class="fw-bold mb-3">
                    Too Many Requests
                </h4>

                <p class="text-600 mb-4">
                    Too many login attempts were detected.
                    Please wait a moment before trying again.
                </p>

                <a href="{{ url('/login') }}" class="btn btn-primary px-4">
                    Back to Login
                </a>

            </div>

        </div>
    </main>
@endsection
