@extends('layouts.landing')

@section('title', '419 Session Expired')

@section('content')

    <main class="main bg-body-tertiary min-vh-100 d-flex align-items-center">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-lg-5 col-md-7">

                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

                        {{-- HEADER --}}
                        <div class="bg-info-subtle text-center py-5">

                            <div class="display-1 fw-black text-info mb-2">
                                419
                            </div>

                            <h3 class="fw-bold text-dark mb-1">
                                Session Expired
                            </h3>

                            <p class="text-secondary mb-0 px-4">
                                Your session has expired due to inactivity or security timeout.
                            </p>

                        </div>

                        {{-- BODY --}}
                        <div class="card-body p-4 p-lg-5 text-center">

                            <div class="mb-4">

                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-info-subtle"
                                    style="width:80px;height:80px;">

                                    <span class="fas fa-clock text-info fs-1"></span>

                                </div>

                            </div>

                            <h5 class="fw-semibold mb-2">
                                Please Refresh and Try Again
                            </h5>

                            <p class="text-600 mb-4">
                                For security purposes, your login session has expired.
                                Refresh the page or login again to continue.
                            </p>

                            <div class="d-grid gap-2">

                                <a href="{{ url()->current() }}" class="btn btn-info rounded-pill text-white">

                                    <span class="fas fa-sync-alt me-2"></span>
                                    Refresh Page

                                </a>

                                <a href="{{ route('login') }}" class="btn btn-outline-secondary rounded-pill">

                                    <span class="fas fa-sign-in-alt me-2"></span>
                                    Login Again

                                </a>

                            </div>

                        </div>

                    </div>

                    <div class="text-center mt-4">

                        <small class="text-500">
                            © {{ date('Y') }} Jell Group of Company
                        </small>

                    </div>

                </div>

            </div>

        </div>

    </main>

@endsection
