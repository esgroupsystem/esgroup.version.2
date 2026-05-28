@extends('layouts.landing')

@section('title', '401 Unauthorized')

@section('content')

    <main class="main bg-body-tertiary min-vh-100 d-flex align-items-center">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-lg-5 col-md-7">

                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

                        {{-- TOP HEADER --}}
                        <div class="bg-primary-subtle text-center py-5">

                            <div class="display-1 fw-black text-primary mb-2">
                                401
                            </div>

                            <h3 class="fw-bold text-dark mb-1">
                                Unauthorized Access
                            </h3>

                            <p class="text-secondary mb-0 px-4">
                                You must login first before accessing this page or module.
                            </p>

                        </div>

                        {{-- BODY --}}
                        <div class="card-body p-4 p-lg-5 text-center">

                            <div class="mb-4">

                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle"
                                    style="width:80px;height:80px;">

                                    <span class="fas fa-lock text-danger fs-1"></span>

                                </div>

                            </div>

                            <h5 class="fw-semibold mb-2">
                                Authentication Required
                            </h5>

                            <p class="text-600 mb-4">
                                Your session may have expired or you do not have permission to continue.
                            </p>

                            <div class="d-grid gap-2">

                                <a href="{{ route('login') }}" class="btn btn-primary btn-lg rounded-pill">

                                    <span class="fas fa-sign-in-alt me-2"></span>
                                    Login Now

                                </a>

                                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill">

                                    <span class="fas fa-arrow-left me-2"></span>
                                    Go Back

                                </a>

                            </div>

                        </div>

                    </div>

                    {{-- FOOTER --}}
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
