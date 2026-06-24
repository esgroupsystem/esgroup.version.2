@extends('layouts.app')
@section('title', 'Dashboard | Jell Group')

@section('content')

    <style>
        body {
            overflow: hidden;
        }

        .welcome-page {
            position: relative;
            overflow: hidden;
            height: calc(100vh - 4.7rem);
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        .welcome-page::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 90px;
            pointer-events: none;
            z-index: 0;

            background: linear-gradient(to bottom,
                    rgba(248, 250, 252, 0.95),
                    rgba(248, 250, 252, 0));
        }

        .welcome-main-grid,
        .content {
            position: relative;
            z-index: 1;
        }

        .welcome-main-grid {
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .welcome-hero-section {
            flex: 1.45;
            min-height: 0;
            padding-top: .25rem;
        }

        .welcome-bottom-section {
            flex: .95;
            min-height: 0;
        }

        .welcome-hero-card {
            height: 100%;
            border: 0;
            border-radius: 1.25rem;
            overflow: hidden;
            background:
                radial-gradient(circle at 90% 10%, rgba(44, 123, 229, .14), transparent 30%),
                radial-gradient(circle at 8% 92%, rgba(0, 210, 122, .12), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f9fcff 100%);
            /* box-shadow: 0 1rem 2rem rgba(18, 38, 63, .07); */
        }

        .welcome-pill {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .85rem;
            border-radius: 999px;
            background: rgba(44, 123, 229, .10);
            color: #2c7be5;
            font-weight: 700;
            font-size: .78rem;
        }

        .welcome-title {
            font-size: clamp(2rem, 3.7vw, 3.1rem);
            line-height: 1.04;
            letter-spacing: -.04em;
            color: #12263f;
        }

        .welcome-subtitle {
            max-width: 650px;
            color: #5e6e82;
            font-size: .98rem;
            line-height: 1.65;
        }

        .welcome-name-gradient {
            background: linear-gradient(90deg, #2c7be5, #00d27a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-wave {
            display: inline-block;
            transform-origin: 70% 70%;
            animation: welcomeWave 1.8s ease-in-out infinite;
        }

        @keyframes welcomeWave {

            0%,
            100% {
                transform: rotate(0deg);
            }

            15% {
                transform: rotate(16deg);
            }

            30% {
                transform: rotate(-8deg);
            }

            45% {
                transform: rotate(14deg);
            }

            60% {
                transform: rotate(-4deg);
            }

            75% {
                transform: rotate(8deg);
            }
        }

        .welcome-avatar-stage {
            min-height: 250px;
            height: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .welcome-orbit {
            position: absolute;
            width: min(280px, 78%);
            aspect-ratio: 1 / 1;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(44, 123, 229, .12), rgba(0, 210, 122, .12));
            animation: welcomeFloat 5s ease-in-out infinite;
        }

        .welcome-orbit::before,
        .welcome-orbit::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 .5rem 1.25rem rgba(18, 38, 63, .08);
        }

        .welcome-orbit::before {
            width: 54px;
            height: 54px;
            top: 24px;
            right: 22px;
        }

        .welcome-orbit::after {
            width: 36px;
            height: 36px;
            left: 22px;
            bottom: 42px;
        }

        @keyframes welcomeFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        .employee-illustration {
            width: 190px;
            height: 235px;
            position: relative;
            z-index: 2;
            animation: employeePop 850ms ease-out both;
        }

        @keyframes employeePop {
            from {
                opacity: 0;
                transform: translateY(20px) scale(.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .employee-head {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            background: #f8c7a3;
            position: absolute;
            top: 15px;
            left: 54px;
            box-shadow: inset 0 -5px 0 rgba(0, 0, 0, .04);
        }

        .employee-hair {
            width: 88px;
            height: 44px;
            border-radius: 48px 48px 18px 18px;
            background: #253858;
            position: absolute;
            top: 6px;
            left: 51px;
        }

        .employee-face-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #12263f;
            position: absolute;
            top: 50px;
        }

        .employee-face-dot.left {
            left: 78px;
        }

        .employee-face-dot.right {
            left: 107px;
        }

        .employee-smile {
            width: 28px;
            height: 14px;
            border-bottom: 3px solid #12263f;
            border-radius: 0 0 22px 22px;
            position: absolute;
            top: 68px;
            left: 81px;
        }

        .employee-body {
            width: 128px;
            height: 120px;
            border-radius: 34px 34px 28px 28px;
            background: linear-gradient(160deg, #2c7be5, #1a68d1);
            position: absolute;
            top: 100px;
            left: 31px;
            box-shadow: 0 1rem 2rem rgba(44, 123, 229, .22);
        }

        .employee-shirt {
            width: 48px;
            height: 64px;
            background: #ffffff;
            position: absolute;
            top: 100px;
            left: 71px;
            clip-path: polygon(0 0, 100% 0, 75% 100%, 25% 100%);
        }

        .employee-arm {
            width: 34px;
            height: 96px;
            border-radius: 22px;
            background: #f8c7a3;
            position: absolute;
            top: 107px;
        }

        .employee-arm.left {
            left: 14px;
            transform: rotate(12deg);
        }

        .employee-arm.right {
            right: 12px;
            top: 70px;
            height: 108px;
            transform-origin: bottom center;
            animation: employeeHandWave 1.7s ease-in-out infinite;
        }

        .employee-hand {
            width: 39px;
            height: 39px;
            border-radius: 50%;
            background: #f8c7a3;
            position: absolute;
            right: 0;
            top: 50px;
            animation: employeeHandWave 1.7s ease-in-out infinite;
            transform-origin: bottom center;
        }

        @keyframes employeeHandWave {

            0%,
            100% {
                transform: rotate(-8deg);
            }

            50% {
                transform: rotate(20deg);
            }
        }

        .welcome-animation-lane {
            position: relative;
            max-width: 640px;
            height: 86px;
            margin-top: 1.15rem;
            border-radius: 1rem;
            overflow: hidden;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .84), rgba(255, 255, 255, .45)),
                linear-gradient(90deg, rgba(44, 123, 229, .08), rgba(0, 210, 122, .08));
            border: 1px solid rgba(44, 123, 229, .08);
        }

        .welcome-animation-lane::before {
            content: "";
            position: absolute;
            left: 1rem;
            right: 1rem;
            bottom: 18px;
            height: 4px;
            border-radius: 999px;
            background: repeating-linear-gradient(90deg,
                    rgba(94, 110, 130, .20) 0,
                    rgba(94, 110, 130, .20) 24px,
                    transparent 24px,
                    transparent 40px);
        }

        .cartoon-bus {
            position: absolute;
            left: 14px;
            bottom: 22px;
            width: 116px;
            height: 44px;
            border-radius: 17px 20px 12px 12px;
            background: linear-gradient(135deg, #2c7be5, #1769d8);
            box-shadow: 0 .7rem 1.25rem rgba(44, 123, 229, .24);
            animation: busMove 5.2s ease-in-out infinite alternate;
        }

        .cartoon-bus::before {
            content: "";
            position: absolute;
            top: 8px;
            left: 14px;
            width: 66px;
            height: 15px;
            border-radius: 8px;
            background: linear-gradient(90deg, #d9f4ff 0 28%, #ffffff 28% 33%, #d9f4ff 33% 61%, #ffffff 61% 66%, #d9f4ff 66%);
        }

        .cartoon-bus::after {
            content: "";
            position: absolute;
            top: 23px;
            right: 10px;
            width: 12px;
            height: 7px;
            border-radius: 999px;
            background: #ffd166;
            box-shadow: 0 0 .75rem rgba(255, 209, 102, .65);
        }

        .bus-wheel {
            position: absolute;
            bottom: -7px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #253858;
            border: 5px solid #ffffff;
            box-shadow: 0 .35rem .75rem rgba(18, 38, 63, .18);
        }

        .bus-wheel.left {
            left: 20px;
        }

        .bus-wheel.right {
            right: 20px;
        }

        @keyframes busMove {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(390px);
            }
        }

        .floating-card {
            position: absolute;
            width: 70px;
            height: 46px;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 .65rem 1.2rem rgba(18, 38, 63, .08);
            border: 1px solid rgba(44, 123, 229, .08);
            animation: cardFloat 3.8s ease-in-out infinite;
        }

        .floating-card::before {
            content: "";
            position: absolute;
            left: 12px;
            right: 12px;
            top: 12px;
            height: 5px;
            border-radius: 999px;
            background: rgba(44, 123, 229, .24);
            box-shadow: 0 12px 0 rgba(0, 210, 122, .20);
        }

        .floating-card.one {
            top: 13px;
            left: 170px;
        }

        .floating-card.two {
            top: 19px;
            right: 138px;
            animation-delay: .45s;
        }

        .floating-card.three {
            top: 11px;
            right: 35px;
            animation-delay: .75s;
        }

        @keyframes cardFloat {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-8px) rotate(-2deg);
            }
        }

        .welcome-bottom-grid {
            height: 100%;
        }

        .welcome-mini-card,
        .welcome-quote-card {
            border: 0;
            border-radius: 1.15rem;
            box-shadow: 0 .5rem 1.25rem rgba(18, 38, 63, .06);
            transition: all .25s ease;
            overflow: hidden;
        }

        .welcome-mini-card {
            background:
                radial-gradient(circle at 92% 12%, rgba(44, 123, 229, .09), transparent 28%),
                #ffffff;
        }

        .welcome-quote-card {
            background:
                radial-gradient(circle at 95% 10%, rgba(0, 210, 122, .08), transparent 30%),
                linear-gradient(135deg, #ffffff, #f9fafd);
        }

        .welcome-mini-card:hover,
        .welcome-quote-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 1rem 2rem rgba(18, 38, 63, .10);
        }

        .welcome-icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            flex: 0 0 auto;
        }

        .welcome-card-body-fit {
            height: 100%;
            display: flex;
            align-items: center;
        }

        .welcome-card-label {
            color: #748194;
            font-size: .75rem;
            font-weight: 600;
            margin-bottom: .2rem;
        }

        .welcome-card-value {
            font-size: .9rem;
            font-weight: 800;
            margin-bottom: 0;
        }

        .fade-up-soft {
            animation: fadeUpSoft .75s ease-out both;
        }

        .fade-up-soft.delay-1 {
            animation-delay: .08s;
        }

        .fade-up-soft.delay-2 {
            animation-delay: .16s;
        }

        /* FORCE PASSWORD CLEAN OVERLAY */
        .force-password-overlay {
            position: fixed;
            inset: 0;
            z-index: 2147483000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(18, 38, 63, .55);
            pointer-events: auto;
        }

        .force-password-card {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 24px 70px rgba(18, 38, 63, .35);
            overflow: hidden;
            pointer-events: auto;
        }

        .force-password-header {
            padding: 18px 22px;
            background: #ffffff;
            border-bottom: 1px solid #edf2f9;
        }

        .force-password-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #12263f;
        }

        .force-password-body {
            padding: 22px;
        }

        .force-password-footer {
            padding: 16px 22px;
            background: #ffffff;
            border-top: 1px solid #edf2f9;
        }

        .force-password-card .form-control {
            height: 42px;
            border-radius: 8px;
        }

        .force-password-card .btn {
            height: 42px;
            border-radius: 8px;
            font-weight: 700;
        }

        .force-password-overlay input,
        .force-password-overlay button {
            pointer-events: auto !important;
        }


        @keyframes fadeUpSoft {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1399.98px) {
            @keyframes busMove {
                from {
                    transform: translateX(0);
                }

                to {
                    transform: translateX(315px);
                }
            }

            .welcome-animation-lane {
                max-width: 560px;
                height: 78px;
            }
        }

        @media (max-width: 991.98px) {
            body {
                overflow: auto;
            }

            .welcome-page {
                height: auto;
                overflow: visible;
            }

            .welcome-main-grid {
                height: auto;
            }

            .welcome-hero-section,
            .welcome-bottom-section {
                flex: unset;
            }

            .welcome-avatar-stage {
                min-height: 230px;
            }

            .welcome-orbit {
                width: 225px;
                height: 225px;
            }

            .employee-illustration {
                transform: scale(.88);
            }

            .welcome-animation-lane {
                max-width: 100%;
            }

            @keyframes busMove {
                from {
                    transform: translateX(0);
                }

                to {
                    transform: translateX(260px);
                }
            }
        }
    </style>

    <div class="container" data-layout="container">
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));

            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content welcome-page">

            @php
                date_default_timezone_set('Asia/Manila');

                $user = auth()->user();
                $hour = now('Asia/Manila')->format('H');

                if ($hour < 12) {
                    $greeting = 'Good Morning';
                    $greetingIcon = 'fas fa-sun';
                    $shortMessage = 'A fresh start for a productive day.';
                } elseif ($hour < 18) {
                    $greeting = 'Good Afternoon';
                    $greetingIcon = 'fas fa-cloud-sun';
                    $shortMessage = 'Keep going, you are doing great today.';
                } else {
                    $greeting = 'Good Evening';
                    $greetingIcon = 'fas fa-moon';
                    $shortMessage = 'Thank you for your hard work today.';
                }

                $today = now('Asia/Manila')->format('l, F d, Y');

                $lastLogin = $user->last_online
                    ? \Carbon\Carbon::parse($user->last_online)->timezone('Asia/Manila')->format('M d, Y h:i A')
                    : 'First time login';

                $displayName = $user->full_name ?? ($user->name ?? 'System');
                $accountStatus = $user->account_status ?? 'active';
            @endphp

            <div class="welcome-main-grid">

                {{-- HERO WELCOME --}}
                <div class="welcome-hero-section">
                    <div class="card welcome-hero-card fade-up-soft">
                        <div class="card-body h-100 p-4 p-xl-5">
                            <div class="row align-items-center h-100 g-4">

                                {{-- LEFT CONTENT --}}
                                <div class="col-lg-7">

                                    <div class="welcome-pill mb-3">
                                        <span class="{{ $greetingIcon }}"></span>
                                        <span>{{ $today }}</span>
                                    </div>

                                    <h1 class="welcome-title fw-black mb-3">
                                        {{ $greeting }},
                                        <br>
                                        <span class="welcome-name-gradient">
                                            {{ $displayName }}
                                        </span>
                                        <span class="welcome-wave">👋</span>
                                    </h1>

                                    <p class="welcome-subtitle mb-3">
                                        Welcome back to Jell Group. We are happy to see you again.
                                        Take a moment, settle in, and have a smooth day ahead.
                                    </p>

                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <span class="badge rounded-pill badge-soft-primary text-primary px-3 py-2">
                                            <span class="fas fa-heart me-1"></span>
                                            Welcome back
                                        </span>

                                        <span class="badge rounded-pill badge-soft-success text-success px-3 py-2">
                                            <span class="fas fa-check-circle me-1"></span>
                                            Account ready
                                        </span>

                                        <span class="badge rounded-pill badge-soft-info text-info px-3 py-2">
                                            <span class="fas fa-smile me-1"></span>
                                            {{ $shortMessage }}
                                        </span>
                                    </div>

                                    {{-- CARTOON ANIMATION BELOW BADGES --}}
                                    <div class="welcome-animation-lane">
                                        <div class="floating-card one"></div>
                                        <div class="floating-card two"></div>
                                        <div class="floating-card three"></div>

                                        <div class="cartoon-bus">
                                            <span class="bus-wheel left"></span>
                                            <span class="bus-wheel right"></span>
                                        </div>
                                    </div>

                                </div>

                                {{-- RIGHT ILLUSTRATION --}}
                                <div class="col-lg-5">
                                    <div class="welcome-avatar-stage">

                                        <div class="welcome-orbit"></div>

                                        <div class="employee-illustration">
                                            <div class="employee-hair"></div>
                                            <div class="employee-head"></div>
                                            <div class="employee-face-dot left"></div>
                                            <div class="employee-face-dot right"></div>
                                            <div class="employee-smile"></div>
                                            <div class="employee-body"></div>
                                            <div class="employee-shirt"></div>
                                            <div class="employee-arm left"></div>
                                            <div class="employee-arm right"></div>
                                            <div class="employee-hand"></div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- FULL SCREEN FITTED STATUS AREA --}}
                <div class="welcome-bottom-section">
                    <div class="row g-3 welcome-bottom-grid">

                        {{-- TOP STATUS CARDS --}}
                        <div class="col-md-4">
                            <div class="card welcome-mini-card h-100 fade-up-soft delay-1">
                                <div class="card-body welcome-card-body-fit p-4">
                                    <div class="d-flex align-items-center gap-3 w-100">
                                        <div class="welcome-icon bg-success-subtle text-success">
                                            <span class="fas fa-circle-check"></span>
                                        </div>

                                        <div>
                                            <p class="welcome-card-label">Today’s Status</p>
                                            <h6 class="welcome-card-value text-success">
                                                Ready to go
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card welcome-mini-card h-100 fade-up-soft delay-1">
                                <div class="card-body welcome-card-body-fit p-4">
                                    <div class="d-flex align-items-center gap-3 w-100">
                                        <div class="welcome-icon bg-warning-subtle text-warning">
                                            <span class="fas fa-clock"></span>
                                        </div>

                                        <div>
                                            <p class="welcome-card-label">Last Login</p>
                                            <h6 class="welcome-card-value text-dark">
                                                {{ $lastLogin }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card welcome-mini-card h-100 fade-up-soft delay-1">
                                <div class="card-body welcome-card-body-fit p-4">
                                    <div class="d-flex align-items-center gap-3 w-100">
                                        <div class="welcome-icon bg-info-subtle text-info">
                                            <span class="fas fa-user-check"></span>
                                        </div>

                                        <div>
                                            <p class="welcome-card-label">Account Status</p>
                                            <h6 class="welcome-card-value text-info text-capitalize">
                                                {{ $accountStatus }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BOTTOM REMINDER CARDS --}}
                        <div class="col-lg-8">
                            <div class="card welcome-quote-card h-100 fade-up-soft delay-2">
                                <div class="card-body welcome-card-body-fit p-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="welcome-icon bg-primary-subtle text-primary">
                                            <span class="fas fa-quote-left"></span>
                                        </div>

                                        <div>
                                            <h5 class="fw-bold mb-2">A little reminder for today</h5>
                                            <p class="text-muted mb-0">
                                                Small progress is still progress. Keep your day simple, focused,
                                                and steady.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card welcome-quote-card h-100 fade-up-soft delay-2">
                                <div class="card-body welcome-card-body-fit p-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="welcome-icon bg-warning-subtle text-warning">
                                            <span class="fas fa-mug-hot"></span>
                                        </div>

                                        <div>
                                            <h5 class="fw-bold mb-2">Take it easy</h5>
                                            <p class="text-muted mb-0">
                                                Breathe, organize, and start one task at a time.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>

    @if (auth()->user()->must_change_password)
        {{-- FORCE PASSWORD OVERLAY --}}
        <div class="force-password-overlay" id="forcePasswordOverlay">
            <div class="force-password-card">

                <form action="{{ route('auth.change.password.update') }}" method="POST">
                    @csrf

                    <div class="force-password-header">
                        <h5 class="force-password-title">
                            <span class="fas fa-lock text-primary me-2"></span>
                            Change Your Password
                        </h5>
                    </div>

                    <div class="force-password-body">
                        <p class="text-muted fs-9 mb-3">
                            Please change your password to continue using the system.
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                New Password
                            </label>

                            <input type="password" name="password" id="forcePasswordInput"
                                class="form-control @error('password') is-invalid @enderror" required
                                autocomplete="new-password">

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">
                                Confirm Password
                            </label>

                            <input type="password" name="password_confirmation" class="form-control" required
                                autocomplete="new-password">
                        </div>
                    </div>

                    <div class="force-password-footer">
                        <button class="btn btn-primary w-100" type="submit">
                            <span class="fas fa-save me-1"></span>
                            Update Password
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const overlay = document.getElementById('forcePasswordOverlay');
                const passwordInput = document.getElementById('forcePasswordInput');

                if (overlay) {
                    document.body.appendChild(overlay);
                }

                document.body.style.overflow = 'hidden';

                if (passwordInput) {
                    setTimeout(function() {
                        passwordInput.focus();
                    }, 150);
                }
            });
        </script>
    @endif

@endsection
