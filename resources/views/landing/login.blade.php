@extends('layouts.landing')

@php
    $nonce = app()->bound('csp_nonce') ? app('csp_nonce') : '';
@endphp

@section('body_class', 'auth-login-body')

@section('content')
    <main class="main auth-login-page" id="top">
        <div class="container-fluid px-0 h-100">
            <div class="row g-0 h-100 bg-100">

                {{-- LEFT LOGIN SIDE --}}
                <div class="col-lg-5 col-xl-4 d-flex align-items-center justify-content-center px-3 px-sm-4">
                    <div class="card auth-login-card shadow-lg border-0 mx-auto overflow-hidden">
                        <div class="card-header bg-primary bg-gradient text-center py-3">
                            <h3 class="text-white fw-bolder mb-1">JELL GROUP</h3>
                            <p class="text-white opacity-75 mb-0 fs-10">Employee Transport Portal</p>
                        </div>

                        <div class="card-body p-4">
                            <div class="mb-3">
                                <h3 class="fw-bold mb-1">Account Login</h3>
                                <p class="text-600 mb-0 fs-10">Verify Cloudflare to continue.</p>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger small rounded-3 py-2">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                                @csrf

                                <div class="mb-2">
                                    <label class="form-label" for="username">Username</label>
                                    <input class="form-control" id="username" name="username" type="text"
                                        value="{{ old('username') }}" required autofocus>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label" for="password">Password</label>
                                    <div class="position-relative">
                                        <input class="form-control pe-5" id="password" name="password" type="password"
                                            required>
                                        <span id="togglePassword" class="password-eye">👁</span>
                                    </div>
                                </div>

                                <div class="row flex-between-center mb-2">
                                    <div class="col-auto">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                            <label class="form-check-label mb-0 fs-10" for="remember">Remember me</label>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <a class="fs-10" href="#">Forgot Password?</a>
                                    </div>
                                </div>

                                <div class="card bg-light border border-300 mb-2">
                                    <div class="card-body py-2 d-flex justify-content-center align-items-center">
                                        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"
                                            data-callback="turnstileSuccess" data-expired-callback="turnstileExpired"
                                            data-error-callback="turnstileExpired">
                                        </div>
                                    </div>
                                </div>

                                <div id="turnstileStatus" class="text-danger fs-10 text-center mb-2">
                                    Complete Cloudflare verification to enable login.
                                </div>

                                @error('turnstile')
                                    <div class="text-danger small mt-1 text-center">{{ $message }}</div>
                                @enderror

                                <button id="loginBtn" class="btn btn-primary d-block w-100 mt-2" type="submit" disabled>
                                    Log in
                                </button>
                            </form>

                            <div class="position-relative mt-3">
                                <hr class="bg-300">
                                <div class="divider-content-center">or log in with</div>
                            </div>

                            <div class="row g-2 mt-2">
                                <div class="col-sm-6">
                                    <a class="btn btn-outline-google-plus btn-sm d-block w-100" href="#">Google</a>
                                </div>
                                <div class="col-sm-6">
                                    <a class="btn btn-outline-facebook btn-sm d-block w-100" href="#">Facebook</a>
                                </div>
                            </div>

                            <div class="text-center mt-3 fs-10 text-600">
                                <span class="fw-semi-bold">Don’t have an account?</span>
                                <a href="#">Register now</a>
                            </div>

                            <p class="text-center fs-10 text-600 mt-2 mb-0">
                                By logging in, you agree to our
                                <a href="#">Terms</a> &
                                <a href="#">Privacy Policy</a>.
                            </p>
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
                            EDSA Carousel Philippines
                        </div>

                        <div class="transport-hero-copy auth-fade-up">
                            <div class="transport-kicker">
                                <span class="transport-bus">🚌</span>
                                <span id="transportWord">Safe Travel</span>
                            </div>

                            <h1 class="fw-bolder mb-3 auth-title">
                                Moving People <span>Safely,</span><br>
                                One Journey at a Time.
                            </h1>
                        </div>

                        <div class="card border-0 shadow-lg bg-white bg-opacity-75 auth-quote-card auth-fade-up">
                            <div class="card-body p-4">
                                <p class="mb-0 fw-semibold text-900 auth-quote-text">
                                    “Every journey matters. We move with safety, serve with respect,
                                    and carry every client toward a better destination.”
                                </p>
                            </div>
                        </div>

                        <div class="row g-3 mt-3 auth-fade-up">
                            <div class="col-md-4">
                                <div class="card bg-white bg-opacity-75 border-0 shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <span class="fas fa-shield-alt text-primary fs-4 mb-2"></span>
                                        <h6 class="fw-bold mb-1">Safe Travel</h6>
                                        <p class="text-700 fs-10 mb-0">Security and care in every route.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card bg-white bg-opacity-75 border-0 shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <span class="fas fa-bus text-primary fs-4 mb-2"></span>
                                        <h6 class="fw-bold mb-1">Reliable Fleet</h6>
                                        <p class="text-700 fs-10 mb-0">Prepared to serve passengers daily.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card bg-white bg-opacity-75 border-0 shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <span class="fas fa-handshake text-primary fs-4 mb-2"></span>
                                        <h6 class="fw-bold mb-1">Client First</h6>
                                        <p class="text-700 fs-10 mb-0">Service built with trust and respect.</p>
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
            window.turnstileSuccess = function() {
                const loginBtn = document.getElementById('loginBtn');
                const turnstileStatus = document.getElementById('turnstileStatus');

                loginBtn.disabled = false;
                loginBtn.classList.remove('disabled');
                turnstileStatus.classList.remove('text-danger');
                turnstileStatus.classList.add('text-success');
                turnstileStatus.textContent = 'Cloudflare verified. You can now log in.';
            };

            window.turnstileExpired = function() {
                const loginBtn = document.getElementById('loginBtn');
                const turnstileStatus = document.getElementById('turnstileStatus');

                loginBtn.disabled = true;
                loginBtn.classList.add('disabled');
                turnstileStatus.classList.remove('text-success');
                turnstileStatus.classList.add('text-danger');
                turnstileStatus.textContent = 'Please verify Cloudflare again before login.';
            };
        </script>

        <script nonce="{{ $nonce }}">
            document.addEventListener('DOMContentLoaded', function() {
                const word = document.getElementById('transportWord');
                if (!word) return;

                const words = [
                    'Safe Travel',
                    'Reliable Routes',
                    'Client Care',
                    'Commuter Trust',
                    'Better Journeys'
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
                const toggle = document.getElementById('togglePassword');
                const password = document.getElementById('password');

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
