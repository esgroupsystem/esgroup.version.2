@extends('layouts.auth')

@php
    $nonce = app()->bound('csp_nonce') ? app('csp_nonce') : '';
    $lockUser = Auth::user();
    $displayName = $lockUser?->full_name ?: ($lockUser?->name ?: $lockUser?->username);
@endphp

@section('title', 'Session locked | Jell Group of Company')
@section('body_class', 'lx-body')

@push('styles')
    @include('layouts.partials.auth-styles')
    <style>
        /* This headline is longer than the login one; let it wrap instead of running under the card. */
        .lx-title > span {
            white-space: normal;
        }

        .lx-avatar {
            width: 5.2em;
            height: 5.2em;
            margin: 0 auto 1em;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 10px 24px -10px rgba(15, 23, 42, .5), 0 0 0 1px rgba(15, 23, 42, .08);
        }

        .lx-lock-badge {
            display: inline-flex;
            align-items: center;
            gap: .4em;
            margin-top: .9em;
            padding: .3em .75em;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: .72em;
            font-weight: 600;
        }

        .lx-lock-badge svg {
            width: 1em;
            height: 1em;
        }

        .lx-signout {
            border: 0;
            background: none;
            padding: 0;
            font: inherit;
            cursor: pointer;
        }
    </style>
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
                    Locked Safe <i></i> Reliable Access <i></i> Secure Service
                </div>

                <h1 class="lx-title">
                    <span>Safety Starts Here,</span>
                    <span><em>Access</em> Continues Securely.</span>
                </h1>

                <p class="lx-lead">
                    Every journey deserves protection. We secure every session, every route, and every client we serve.
                </p>

                <div class="lx-hero-foot">
                    <div class="lx-pillars">
                        <div class="lx-pillar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            <strong>Locked Safe</strong>
                            <span>Your account stays protected</span>
                        </div>
                        <div class="lx-pillar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 6v6" /><path d="M15 6v6" /><path d="M2 12h19.6" />
                                <path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3" />
                                <circle cx="7" cy="18" r="2" /><path d="M9 18h5" /><circle cx="16" cy="18" r="2" />
                            </svg>
                            <strong>Reliable Access</strong>
                            <span>Ready when duty calls</span>
                        </div>
                        <div class="lx-pillar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /><path d="m9 12 2 2 4-4" />
                            </svg>
                            <strong>Secure Service</strong>
                            <span>Protection for every workflow</span>
                        </div>
                    </div>
                    <div class="lx-system"><span>Secure Employee Session</span></div>
                </div>
            </section>

            {{-- RIGHT: unlock card --}}
            <div class="lx-card-wrap">
                <div class="lx-card">
                    <div class="lx-card-head">
                        <img class="lx-avatar" src="{{ asset('assets/img/lockscreen/profile_lockscreen.jpg') }}" alt="">
                        <div class="lx-card-brand">Jell Group of Company</div>
                        <h2 class="lx-card-title">Hello, {{ $displayName }}</h2>
                        <p class="lx-card-sub">Your session is locked. Enter your password to continue.</p>
                        <span class="lx-lock-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            Session locked
                        </span>
                    </div>

                    {{-- Errors show as top-right toasts (layouts.partials.toasts). --}}

                    <form method="POST" action="{{ route('lockscreen.unlock') }}" id="lockscreenForm">
                        @csrf

                        <div class="lx-field">
                            <label class="lx-label" for="lockPassword">Password</label>
                            <div class="lx-input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                <input class="lx-input" id="lockPassword" name="password" type="password"
                                    placeholder="Enter your password" autocomplete="current-password" required autofocus>
                                <button type="button" id="toggleLockPassword" class="lx-eye" aria-label="Show password" aria-pressed="false">
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

                        <div class="lx-verify">
                            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"
                                data-theme="light" data-callback="turnstileSuccess"
                                data-expired-callback="turnstileExpired" data-error-callback="turnstileExpired">
                            </div>
                        </div>

                        @error('turnstile')
                            <p class="lx-field-error">{{ $message }}</p>
                        @enderror

                        <button class="lx-submit" type="submit" style="margin-top: .6em;">
                            <span>Unlock Session</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14" /><path d="m12 5 7 7-7 7" />
                            </svg>
                        </button>
                    </form>

                    <p class="lx-help">
                        Not you?
                        <button type="submit" form="logoutForm" class="lx-link lx-signout">Sign out</button>
                    </p>

                    <form id="logoutForm" action="{{ route('logout') }}" method="POST" hidden>
                        @csrf
                    </form>
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
            // The widget calls these; unlocking itself only checks the password (UnlockRequest).
            window.turnstileSuccess = function() {};
            window.turnstileExpired = function() {};

            document.addEventListener('DOMContentLoaded', function() {
                const toggle = document.getElementById('toggleLockPassword');
                const password = document.getElementById('lockPassword');

                if (!toggle || !password) return;

                toggle.addEventListener('click', function() {
                    const show = password.type === 'password';

                    password.type = show ? 'text' : 'password';
                    toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
                    toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                    toggle.querySelector('.lx-eye-open').hidden = show;
                    toggle.querySelector('.lx-eye-closed').hidden = !show;
                });
            });
        </script>
    @endpush
@endsection
