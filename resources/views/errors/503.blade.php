@extends('layouts.landing')

@section('title', '503 Service Unavailable')

@section('content')

    <main class="main bg-body-tertiary min-vh-100 d-flex align-items-center">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-lg-5 col-md-7">

                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

                        {{-- HEADER --}}
                        <div class="bg-secondary-subtle text-center py-5">

                            <div class="display-1 fw-black text-secondary mb-2">
                                503
                            </div>

                            <h3 class="fw-bold text-dark mb-1">
                                Service Temporarily Unavailable
                            </h3>

                            <p class="text-secondary mb-0 px-4">
                                The system is currently under maintenance or temporarily unavailable.
                            </p>

                        </div>

                        {{-- BODY --}}
                        <div class="card-body p-4 p-lg-5 text-center">

                            <div class="mb-4">

                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary-subtle"
                                    style="width:80px;height:80px;">

                                    <span class="fas fa-tools text-secondary fs-1"></span>

                                </div>

                            </div>

                            <h5 class="fw-semibold mb-2">
                                We'll Be Back Soon
                            </h5>

                            <p class="text-600 mb-4">
                                Our team is currently performing maintenance and improvements.
                                Please try again later.
                            </p>

                            <div class="alert alert-light border rounded-3 text-start small mb-4">

                                <div class="fw-semibold mb-2">
                                    Maintenance in progress:
                                </div>

                                <ul class="mb-0 ps-3">
                                    <li>System upgrades and optimization.</li>
                                    <li>Security enhancements.</li>
                                    <li>Performance improvements.</li>
                                    <li>Temporary service interruption.</li>
                                </ul>

                            </div>

                            <div class="d-grid gap-2">

                                <a href="{{ url('/') }}" class="btn btn-secondary rounded-pill">

                                    <span class="fas fa-home me-2"></span>
                                    Return Home

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
