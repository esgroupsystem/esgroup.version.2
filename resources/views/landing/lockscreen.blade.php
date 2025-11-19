@extends('layouts.landing')
@section('content')
<div class="container-fluid">
    <div class="row min-vh-100 flex-center g-0">
        <div class="col-lg-8 col-xxl-5 py-3 position-relative">
            <img class="bg-auth-circle-shape" src="{{ asset('assets/img/icons/spot-illustrations/bg-shape.png') }}" alt="" width="250">
            <img class="bg-auth-circle-shape-2" src="{{ asset('assets/img/icons/spot-illustrations/shape-1.png') }}" alt="" width="150">

            <div class="card overflow-hidden z-1">
                <div class="card-body p-0">
                    <div class="row g-0 h-100">

                        {{-- LEFT SECTION --}}
                        <div class="col-md-5 text-center bg-card-gradient">
                            <div class="position-relative p-4 pt-md-5 pb-md-7" data-bs-theme="light">
                                <div class="bg-holder bg-auth-card-shape"
                                     style="background-image:url({{ asset('assets/img/icons/spot-illustrations/half-circle.png') }});">
                                </div>

                                <div class="z-1 position-relative">
                                    <span class="link-light mb-4 font-sans-serif fs-5 d-inline-block fw-bolder">
                                        ES GROUP
                                    </span>
                                    <p class="opacity-75 text-white">Your session is locked for security.</p>
                                </div>
                            </div>

                            <div class="mt-3 mb-4 mt-md-4 mb-md-5">
                                <p class="mb-0 mt-4 mt-md-5 fs-10 fw-semi-bold text-white opacity-75">
                                    By continuing, you accept our
                                    <a class="text-decoration-underline text-white" href="#">terms</a> and
                                    <a class="text-decoration-underline text-white" href="#">privacy policy</a>
                                </p>
                            </div>
                        </div>

                        {{-- RIGHT SECTION --}}
                        <div class="col-md-7 d-flex flex-center">
                            <div class="p-4 p-md-5 flex-grow-1">

                                <div class="row justify-content-center">
                                    <div class="col-auto">
                                        <div class="d-md-flex align-items-center text-center text-md-start">

                                            {{-- USER AVATAR --}}
                                            <div class="avatar avatar-4xl me-4">
                                                <img class="rounded-circle"
                                                     src="{{ Auth::user()->profile_picture ?? asset('assets/img/team/default.jpg') }}"
                                                     alt="User Avatar">
                                            </div>

                                            {{-- USER INFO --}}
                                            <div class="flex-1">
                                                <h4>Hello, {{ Auth::user()->name ?? Auth::user()->username }}</h4>
                                                <p class="mb-0">Enter your password to unlock your session.</p>
                                            </div>
                                        </div>

                                        {{-- PASSWORD FORM --}}
                                        <form class="mt-4 row gx-2" method="POST" action="{{ route('lockscreen.unlock') }}">
                                            @csrf

                                            <div class="col">
                                                <input class="form-control"
                                                       type="password"
                                                       name="password"
                                                       placeholder="Password"
                                                       required />
                                            </div>

                                            <div class="col-4">
                                                <button class="btn btn-primary d-block w-100" type="submit">
                                                    Login
                                                </button>
                                            </div>
                                        </form>

                                        {{-- Error --}}
                                        @if ($errors->any())
                                            <p class="text-danger small mt-2">{{ $errors->first() }}</p>
                                        @endif

                                    </div>
                                </div>

                            </div>
                        </div>
                        {{-- END RIGHT --}}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
