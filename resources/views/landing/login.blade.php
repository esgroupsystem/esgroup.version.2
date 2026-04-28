@extends('layouts.landing')

@section('content')
    <div class="container-fluid">
        <div class="row min-vh-100 flex-center g-0">
            <div class="col-lg-8 col-xxl-5 py-3 position-relative">
                <img class="bg-auth-circle-shape" src="{{ asset('assets/img/icons/spot-illustrations/bg-shape.png') }}"
                    width="250">
                <img class="bg-auth-circle-shape-2" src="{{ asset('assets/img/icons/spot-illustrations/shape-1.png') }}"
                    width="150">

                <div class="card overflow-hidden z-1">
                    <div class="card-body p-0">
                        <div class="row g-0 h-100">

                            {{-- LEFT SIDE --}}
                            <div class="col-md-5 text-center bg-card-gradient">
                                <div class="position-relative p-4 pt-md-5 pb-md-7" data-bs-theme="light">
                                    <div class="bg-holder bg-auth-card-shape"
                                        style="background-image:url({{ asset('assets/img/icons/spot-illustrations/half-circle.png') }});">
                                    </div>

                                    <div class="z-1 position-relative">
                                        <span class="link-light mb-4 font-sans-serif fs-5 fw-bolder">
                                            ES GROUP
                                        </span>
                                        <p class="opacity-75 text-white">
                                            Secure login for employees and administrators.
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 mb-4 mt-md-4 mb-md-5" data-bs-theme="light">
                                    <p class="text-white">
                                        Don’t have an account?<br>
                                        <a class="text-decoration-underline link-light" href="#">
                                            Register now
                                        </a>
                                    </p>

                                    <p class="mb-0 mt-4 fs-10 fw-semi-bold text-white opacity-75">
                                        By logging in, you agree to our
                                        <a class="text-decoration-underline text-white" href="#">Terms</a> &
                                        <a class="text-decoration-underline text-white" href="#">Privacy Policy</a>
                                    </p>
                                </div>
                            </div>

                            {{-- RIGHT SIDE --}}
                            <div class="col-md-7 d-flex flex-center">
                                <div class="p-4 p-md-5 flex-grow-1">

                                    <h3>Account Login</h3>

                                    {{-- ERRORS --}}
                                    @if ($errors->any())
                                        <div class="alert alert-danger small">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('login.post') }}">
                                        @csrf

                                        {{-- USERNAME --}}
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input class="form-control" name="username" type="text"
                                                value="{{ old('username') }}" required>
                                        </div>

                                        {{-- PASSWORD --}}
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <input class="form-control" name="password" type="password" required>
                                        </div>

                                        {{-- REMEMBER --}}
                                        <div class="row flex-between-center">
                                            <div class="col-auto">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" name="remember">
                                                    <label class="form-check-label">Remember me</label>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <a class="fs-10" href="#">Forgot Password?</a>
                                            </div>
                                        </div>

                                        {{-- 🔐 TURNSTILE CAPTCHA --}}
                                        <div class="mb-3 mt-3">
                                            <div class="cf-turnstile"
                                                data-sitekey="{{ config('services.turnstile.site_key') }}">
                                            </div>

                                            @error('turnstile')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- SUBMIT --}}
                                        <div class="mb-3">
                                            <button class="btn btn-primary w-100 mt-2" type="submit">
                                                Log in
                                            </button>
                                        </div>
                                    </form>

                                    {{-- SOCIAL --}}
                                    <div class="position-relative mt-4">
                                        <hr />
                                        <div class="divider-content-center">or log in with</div>
                                    </div>

                                    <div class="row g-2 mt-2">
                                        <div class="col-sm-6">
                                            <a class="btn btn-outline-google-plus btn-sm w-100" href="#">
                                                Google
                                            </a>
                                        </div>
                                        <div class="col-sm-6">
                                            <a class="btn btn-outline-facebook btn-sm w-100" href="#">
                                                Facebook
                                            </a>
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

@endsection
