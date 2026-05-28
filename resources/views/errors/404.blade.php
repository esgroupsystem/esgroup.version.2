@extends('layouts.landing')

@section('title', '404 Not Found')

@section('content')

    <main class="main bg-body-tertiary min-vh-100 d-flex align-items-center">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-lg-5 col-md-7">

                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

                        {{-- HEADER --}}
                        <div class="bg-primary-subtle text-center py-5">

                            <div class="display-1 fw-black text-primary mb-2">
                                404
                            </div>

                            <h3 class="fw-bold text-dark mb-1">
                                Page Not Found
                            </h3>

                            <p class="text-secondary mb-0 px-4">
                                The page or module you are trying to access does not exist.
                            </p>

                        </div>

                        {{-- BODY --}}
                        <div class="card-body p-4 p-lg-5 text-center">

                            <div class="mb-4">

                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle"
                                    style="width:80px;height:80px;">

                                    <span class="fas fa-search text-primary fs-1"></span>

                                </div>

                            </div>

                            <h5 class="fw-semibold mb-2">
                                We couldn't find that page
                            </h5>

                            <p class="text-600 mb-4">
                                The requested URL may have been removed, renamed,
                                or temporarily unavailable.
                            </p>

                            <div class="alert alert-light border rounded-3 text-start small mb-4">

                                <div class="fw-semibold mb-2">
                                    Possible reasons:
                                </div>

                                <ul class="mb-0 ps-3">
                                    <li>The page URL is incorrect.</li>
                                    <li>The module was moved or deleted.</li>
                                    <li>You do not have access permission.</li>
                                    <li>The route has not been created yet.</li>
                                </ul>

                            </div>

                            <div class="d-grid gap-2">

                                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill">

                                    <span class="fas fa-arrow-left me-2"></span>
                                    Go Back

                                </a>

                                <a href="{{ route('dashboard.index') }}" class="btn btn-warning rounded-pill text-dark">

                                    <span class="fas fa-home me-2"></span>
                                    Return Dashboard

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
