@extends('layouts.landing')

@section('title', '403 Forbidden')

@section('content')

    <main class="main bg-body-tertiary min-vh-100 d-flex align-items-center">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-lg-5 col-md-7">

                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

                        {{-- HEADER --}}
                        <div class="bg-danger-subtle text-center py-5">

                            <div class="display-1 fw-black text-danger mb-2">
                                403
                            </div>

                            <h3 class="fw-bold text-dark mb-1">
                                Access Forbidden
                            </h3>

                            <p class="text-secondary mb-0 px-4">
                                You do not have permission to access this page or module.
                            </p>

                        </div>

                        {{-- BODY --}}
                        <div class="card-body p-4 p-lg-5 text-center">

                            <div class="mb-4">

                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle"
                                    style="width:80px;height:80px;">

                                    <span class="fas fa-ban text-danger fs-1"></span>

                                </div>

                            </div>

                            <h5 class="fw-semibold mb-2">
                                Permission Denied
                            </h5>

                            <p class="text-600 mb-4">
                                Your account currently does not have sufficient privileges
                                to access this resource.
                            </p>

                            <div class="alert alert-light border rounded-3 text-start small mb-4">

                                <div class="fw-semibold mb-2">
                                    Possible reasons:
                                </div>

                                <ul class="mb-0 ps-3">
                                    <li>Your role permissions are restricted.</li>
                                    <li>You are accessing another department's data.</li>
                                    <li>Your session or access level has changed.</li>
                                </ul>

                            </div>

                            <div class="d-grid gap-2">

                                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill">

                                    <span class="fas fa-arrow-left me-2"></span>
                                    Go Back

                                </a>

                                <a href="{{ route('dashboard.index') }}" class="btn btn-danger rounded-pill">

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
