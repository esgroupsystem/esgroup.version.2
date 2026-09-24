@extends('layouts.auth')

@php
    $nonce = app()->bound('csp_nonce') ? app('csp_nonce') : '';
@endphp

@section('title', 'Sign in | Jell Group of Company')
@section('body_class', 'lx-body')

@push('styles')
    @include('layouts.partials.auth-styles')
@endpush

@section('content')
    <main class="lx-page" id="top">
        <img class="lx-bg" src="{{ asset('assets/img/generic/groupes.jpg') }}" alt="" aria-hidden="true">

        <div class="lx-shell">
            {{-- LEFT: brand + message --}}
            <section class="lx-hero" aria-label="Jell Group">
                <div class="lx-brand">
                    <div class="lx-brand-mark">
                        <img src="{{ asset('assets/img/favicons/esgroup-logo180x180.png') }}" alt="">
                    </div>
                    <div class="lx-brand-name">Jell Group of Company</div>
                </div>

                <div class="lx-rule"></div>

                <div class="lx-tagline">
                    Safe Travel <i></i> Reliable Fleet <i></i> Client First
                </div>

                <h1 class="lx-title">
                    <span>Moving People Safely,</span>
                    <span><em>One Journey</em> at a Time.</span>
                </h1>

                <p class="lx-lead">
                    Every journey matters. We move with safety, serve with respect, and carry every client toward a
                    better destination.
                </p>

                <div class="lx-hero-foot">
                    <div class="lx-pillars">
                        <div class="lx-pillar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                            <strong>Safe Travel</strong>
                            <span>Security in every route</span>
                        </div>
                        <div class="lx-pillar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 6v6" /><path d="M15 6v6" /><path d="M2 12h19.6" />
                                <path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3" />
                                <circle cx="7" cy="18" r="2" /><path d="M9 18h5" /><circle cx="16" cy="18" r="2" />
                            </svg>
                            <strong>Reliable Fleet</strong>
                            <span>Ready to serve daily</span>
                        </div>
                        <div class="lx-pillar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <strong>Client First</strong>
                            <span>Built on trust and respect</span>
                        </div>
                    </div>
                    <div class="lx-system"><span>Employee Transport Portal</span></div>
                </div>
            </section>

            {{-- RIGHT: login card --}}
            <div class="lx-card-wrap">
                <div class="lx-card">
                    <div class="lx-card-head">
                        <div class="lx-card-logo">
                            <img src="{{ asset('assets/img/favicons/esgroup-logo180x180.png') }}" alt="">
                        </div>
                        <div class="lx-card-brand">Jell Group of Company</div>
                        <h2 class="lx-card-title">Welcome Back</h2>
                        <p class="lx-card-sub">Sign in to access your company portal</p>
                    </div>

                    {{-- Errors and flash messages show as top-right toasts (layouts.partials.toasts). --}}

                    <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                        @csrf

                        <div class="lx-field">
                            <label class="lx-label" for="username">Username</label>
                            <div class="lx-input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />
                                </svg>
                                <input class="lx-input" id="username" name="username" type="text" value="{{ old('username') }}"
                                    placeholder="Enter your username" autocomplete="username" required autofocus>
                            </div>
                        </div>

                        <div class="lx-field">
                            <label class="lx-label" for="password">Password</label>
                            <div class="lx-input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                <input class="lx-input" id="password" name="password" type="password"
                                    placeholder="Enter your password" autocomplete="current-password" required>
                                <button type="button" id="togglePassword" class="lx-eye" aria-label="Show password" aria-pressed="false">
                                    <svg class="lx-eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" /><circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg class="lx-eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" hidden>
                                        <path d="M9.9 4.2A10.4 10.4 0 0 1 12 4c6.5 0 10 8 10 8a17.6 17.6 0 0 1-2.2 3.2" />
                                        <path d="M6.6 6.6A17.4 17.4 0 0 0 2 12s3.5 8 10 8a9.7 9.7 0 0 0 5.4-1.6" />
                                        <path d="M14.1 14.1a3 3 0 1 1-4.2-4.2" /><path d="m2 2 20 20" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="lx-row">
                            <label class="lx-check" for="remember">
                                <input type="checkbox" id="remember" name="remember" @checked(old('remember'))>
                                Remember me
                            </label>
                            <a class="lx-link" href="#" id="forgotPassword">Forgot Password?</a>
                        </div>

                        <div class="lx-verify">
                            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"
                                data-theme="light" data-callback="turnstileSuccess"
                                data-expired-callback="turnstileExpired" data-error-callback="turnstileExpired">
                            </div>
                        </div>

                        <p id="turnstileStatus" class="lx-status text-danger">
                            Complete the security check to enable sign in.
                        </p>

                        @error('turnstile')
                            <p class="lx-field-error">{{ $message }}</p>
                        @enderror

                        <button id="loginBtn" class="lx-submit" type="submit" disabled>
                            <span id="loginBtnText">Sign In</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14" /><path d="m12 5 7 7-7 7" />
                            </svg>
                        </button>
                    </form>

                    <p class="lx-help">Need help? <a class="lx-link" href="#" id="contactSupport">Contact IT Support</a></p>
                </div>
            </div>
        </div>

        <footer class="lx-footer">
            <span>&copy; {{ now()->year }} Jell Group of Company. All rights reserved.</span>
            <nav aria-label="Legal">
                <span>Privacy</span><span>Terms</span><span>Support</span>
            </nav>
        </footer>
    </main>

    @push('scripts')
        <script nonce="{{ $nonce }}" src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

        <script nonce="{{ $nonce }}">
            window.turnstileSuccess = function() {
                const loginBtn = document.getElementById('loginBtn');
                const turnstileStatus = document.getElementById('turnstileStatus');

                loginBtn.disabled = false;
                loginBtn.classList.remove('disabled');
                turnstileStatus.classList.remove('text-danger');
                turnstileStatus.classList.add('text-success');
                turnstileStatus.textContent = 'Security check passed. You can now sign in.';
            };

            window.turnstileExpired = function() {
                const loginBtn = document.getElementById('loginBtn');
                const turnstileStatus = document.getElementById('turnstileStatus');

                loginBtn.disabled = true;
                loginBtn.classList.add('disabled');
                turnstileStatus.classList.remove('text-success');
                turnstileStatus.classList.add('text-danger');
                turnstileStatus.textContent = 'Please complete the security check again before signing in.';
            };
        </script>

        <script nonce="{{ $nonce }}">
            document.addEventListener('DOMContentLoaded', function() {
                const toggle = document.getElementById('togglePassword');
                const password = document.getElementById('password');

                if (!toggle || !password) return;

                toggle.addEventListener('click', function() {
                    const show = password.type === 'password';

                    password.type = show ? 'text' : 'password';
                    toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
                    toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                    toggle.querySelector('.lx-eye-open').hidden = show;
                    toggle.querySelector('.lx-eye-closed').hidden = !show;
                });

                // Forgot password / IT support have no self-service flow yet; point users to IT.
                ['forgotPassword', 'contactSupport'].forEach(function(id) {
                    const link = document.getElementById(id);
                    if (!link) return;

                    link.addEventListener('click', function(event) {
                        event.preventDefault();
                        const status = document.getElementById('turnstileStatus');
                        status.classList.remove('text-danger');
                        status.classList.add('text-success');
                        status.textContent = 'Please contact the IT Department to reset your password or get help signing in.';
                    });
                });
            });
        </script>

        <script nonce="{{ $nonce }}">
            document.addEventListener('DOMContentLoaded', function() {

                @if (($seconds ?? 0) > 0)

                    let seconds = {{ $seconds ?? 0 }};

                    const loginBtn = document.getElementById('loginBtn');
                    const loginBtnText = document.getElementById('loginBtnText');

                    const loginForm = document.getElementById('loginForm');

                    const username = document.getElementById('username');
                    const password = document.getElementById('password');
                    const remember = document.getElementById('remember');

                    /*
                    |--------------------------------------------------------------------------
                    | LOCK ENTIRE FORM
                    |--------------------------------------------------------------------------
                    */

                    loginBtn.disabled = true;

                    username.disabled = true;
                    password.disabled = true;

                    if (remember) {
                        remember.disabled = true;
                    }

                    loginBtn.classList.add('disabled');

                    loginBtn.style.pointerEvents = 'none';

                    /*
                    |--------------------------------------------------------------------------
                    | PREVENT FORM SUBMIT
                    |--------------------------------------------------------------------------
                    */

                    loginForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        return false;
                    });

                    /*
                    |--------------------------------------------------------------------------
                    | COUNTDOWN
                    |--------------------------------------------------------------------------
                    */

                    loginBtnText.textContent = `Please wait (${seconds}s)`;

                    const timer = setInterval(() => {

                        seconds--;

                        if (seconds <= 0) {

                            clearInterval(timer);

                            location.reload();

                            return;
                        }

                        loginBtnText.textContent = `Please wait (${seconds}s)`;

                    }, 1000);
                @endif

            });
        </script>
    @endpush
@endsection
