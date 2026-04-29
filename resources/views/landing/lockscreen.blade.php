@extends('layouts.landing')

@php
    $nonce = app()->bound('csp_nonce') ? app('csp_nonce') : '';
@endphp

@section('body_class', 'auth-login-body')

@section('content')
    <main class="main auth-login-page" id="top">
        <div class="container-fluid px-0 h-100">
            <div class="row g-0 h-100 bg-100">

                {{-- LEFT LOCKSCREEN SIDE --}}
                <div class="col-lg-5 col-xl-4 d-flex align-items-center justify-content-center px-3 px-sm-4">
                    <div class="card auth-login-card shadow-lg border-0 mx-auto overflow-hidden">
                        <div class="card-header bg-primary bg-gradient text-center py-3">
                            <h3 class="text-white fw-bolder mb-1">JELL GROUP</h3>
                            <p class="text-white opacity-75 mb-0 fs-10">Secure Employee Session</p>
                        </div>

                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="avatar avatar-4xl mx-auto mb-3">
                                    <img class="rounded-circle shadow-sm"
                                        src="{{ asset('assets/img/lockscreen/profile_lockscreen.jpg') }}" alt="User Avatar">
                                </div>

                                <h4 class="fw-bold mb-1">
                                    Hello, {{ Auth::user()->name ?? Auth::user()->username }}
                                </h4>
                                <p class="text-600 mb-0 fs-10">
                                    Your session is locked. Enter your password to continue.
                                </p>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger small rounded-3 py-2">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('lockscreen.unlock') }}" id="lockscreenForm">
                                @csrf

                                <div class="mb-2">
                                    <label class="form-label" for="lockPassword">Password</label>
                                    <div class="position-relative">
                                        <input class="form-control pe-5" id="lockPassword" name="password" type="password"
                                            required autofocus>

                                        <span id="toggleLockPassword" class="password-eye">👁</span>
                                    </div>
                                </div>

                                {{-- CLOUDFLARE TURNSTILE --}}
                                <div class="card bg-light border border-300 mb-2">
                                    <div class="card-body py-2 d-flex justify-content-center align-items-center">
                                        <div class="cf-turnstile"
                                            data-sitekey="{{ config('services.turnstile.site_key') }}">
                                        </div>
                                    </div>
                                </div>

                                @error('turnstile')
                                    <div class="text-danger small mt-1 text-center">{{ $message }}</div>
                                @enderror

                                <button class="btn btn-primary d-block w-100 mt-3" type="submit">
                                    Unlock Session
                                </button>
                            </form>

                            <div class="text-center mt-3 fs-10 text-600">
                                Not you?
                                <a href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                                    Sign out
                                </a>
                            </div>

                            <p class="text-center fs-10 text-600 mt-2 mb-0">
                                Protected access for JELL GROUP employees.
                            </p>

                            <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>

                {{-- RIGHT TRANSPORT DESIGN SIDE --}}
                <div
                    class="col-lg-7 col-xl-8 d-none d-lg-flex align-items-center bg-primary position-relative overflow-hidden auth-transport-side">
                    <img class="position-absolute w-100 h-100 object-fit-cover auth-transport-img"
                        src="{{ asset('assets/img/generic/groupes.jpg') }}" alt="Jell Group Transport">

                    <div class="position-relative z-1 px-5 px-xl-7 text-white auth-transport-content">
                        <div class="badge rounded-pill bg-white text-primary px-3 py-2 mb-3 shadow-sm auth-fade-up">
                            Secure Transport Portal
                        </div>

                        <div class="transport-hero-copy auth-fade-up">
                            <div class="transport-kicker">
                                <span class="transport-bus">🚌</span>
                                <span id="transportWord">Session Protected</span>
                            </div>

                            <h1 class="fw-bolder mb-3 auth-title">
                                Safety Starts <span>Here,</span><br>
                                Access Continues Securely.
                            </h1>
                        </div>

                        <div class="card border-0 shadow-lg bg-white bg-opacity-75 auth-quote-card auth-fade-up">
                            <div class="card-body p-4">
                                <p class="mb-0 fw-semibold text-900 auth-quote-text">
                                    “Every journey deserves protection. We secure every session,
                                    every route, and every client we serve.”
                                </p>
                            </div>
                        </div>

                        <div class="row g-3 mt-3 auth-fade-up">
                            <div class="col-md-4">
                                <div class="card bg-white bg-opacity-75 border-0 shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <span class="fas fa-lock text-primary fs-4 mb-2"></span>
                                        <h6 class="fw-bold mb-1">Locked Safe</h6>
                                        <p class="text-700 fs-10 mb-0">Your account stays protected.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card bg-white bg-opacity-75 border-0 shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <span class="fas fa-bus text-primary fs-4 mb-2"></span>
                                        <h6 class="fw-bold mb-1">Reliable Access</h6>
                                        <p class="text-700 fs-10 mb-0">Ready when duty calls.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card bg-white bg-opacity-75 border-0 shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <span class="fas fa-shield-alt text-primary fs-4 mb-2"></span>
                                        <h6 class="fw-bold mb-1">Secure Service</h6>
                                        <p class="text-700 fs-10 mb-0">Protection for every workflow.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    @push('scripts')
        <script nonce="{{ $nonce }}">
            document.addEventListener('DOMContentLoaded', function() {
                const word = document.getElementById('transportWord');
                if (!word) return;

                const words = [
                    'Session Protected',
                    'Safe Access',
                    'Secure Routes',
                    'Trusted Service',
                    'Ready to Continue'
                ];

                let index = 0;

                setInterval(function() {
                    word.classList.add('transport-word-out');

                    setTimeout(function() {
                        index = (index + 1) % words.length;
                        word.textContent = words[index];

                        word.classList.remove('transport-word-out');
                        word.classList.add('transport-word-in');

                        setTimeout(function() {
                            word.classList.remove('transport-word-in');
                        }, 650);
                    }, 420);
                }, 5000);
            });
        </script>

        <script nonce="{{ $nonce }}">
            document.addEventListener('DOMContentLoaded', function() {
                const toggle = document.getElementById('toggleLockPassword');
                const password = document.getElementById('lockPassword');

                if (!toggle || !password) return;

                toggle.addEventListener('click', function() {
                    const isHidden = password.type === 'password';

                    password.type = isHidden ? 'text' : 'password';
                    this.textContent = isHidden ? '🙈' : '👁';
                });
            });
        </script>
    @endpush
@endsection
