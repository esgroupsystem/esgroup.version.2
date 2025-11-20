@extends('layouts.app')
@section('title', 'Dashboard | Jell Group')

@section('content')

    <div class="container" data-layout="container">
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>
        <div class="content">

            @if (auth()->user()->must_change_password)
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        new bootstrap.Modal(document.getElementById('forceChangePasswordModal')).show();
                    });
                </script>
            @endif
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm position-relative overflow-hidden">

                        <!-- Background Illustration -->
                        <div class="bg-holder"
                            style="
                    background-image:url('assets/img/icons/spot-illustrations/corner-5.png');
                    background-size: 45%;
                    background-position: right top;
                    opacity: .25;
                ">
                        </div>

                        <div class="card-body position-relative py-5 px-4 px-md-5">

                            @php
                                date_default_timezone_set('Asia/Manila');
                                $hour = date('H');
                                if ($hour < 12) {
                                    $greeting = 'Good Morning';
                                } elseif ($hour < 18) {
                                    $greeting = 'Good Afternoon';
                                } else {
                                    $greeting = 'Good Evening';
                                }
                            @endphp

                            <!-- Greeting -->
                            <h2 class="fw-bold text-primary mb-2">
                                {{ $greeting }}, {{ Auth::user()->full_name }}! 👋
                            </h2>

                            <p class="text-700 fs-8 mb-3" style="max-width: 550px;">
                                Welcome back to <strong>Jell Group Dashboard</strong>.
                                We're keeping everything running smoothly so you can work with confidence.
                                Here's your latest account activity and system status.
                            </p>

                            <!-- Status Row -->
                            <div class="row mt-4 g-3">
                                <div class="col-md-4 col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-item icon-item-sm bg-soft-success me-3">
                                            <span class="fas fa-check text-success"></span>
                                        </div>
                                        <div>
                                            <p class="text-600 fs-10 mb-0">System Status</p>
                                            <h6 class="mb-0 text-success fw-semibold">All services operational</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-item icon-item-sm bg-soft-warning me-3">
                                            <span class="fas fa-clock text-warning"></span>
                                        </div>
                                        <div>
                                            <p class="text-600 fs-10 mb-0">Last Login</p>
                                            <h6 class="mb-0 fw-semibold">
                                                {{ Auth::user()->last_online ? Auth::user()->last_online->format('M d, Y h:i A') : 'N/A' }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-12">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-item icon-item-sm bg-soft-info me-3">
                                            <span class="fas fa-user text-info"></span>
                                        </div>
                                        <div>
                                            <p class="text-600 fs-10 mb-0">Account Status</p>
                                            <h6 class="mb-0 fw-semibold text-info text-capitalize">
                                                {{ Auth::user()->account_status }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- card-body -->
                    </div><!-- card -->
                </div><!-- col -->
            </div><!-- row -->

        </div>
    </div>
    <div class="modal fade" id="forceChangePasswordModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">

                <form action="{{ route('auth.change.password.update') }}" method="POST">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Change Your Password</h5>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted fs-9">
                            Please change your password to continue using the system.
                        </p>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary w-100" type="submit">
                            Update Password
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection
