@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">

                    <h4 class="mb-3 fw-bold">Change Password</h4>
                    <p class="text-muted fs-9">
                        You must change your password before accessing the system.
                    </p>

                    <form method="POST" action="{{ route('auth.change.password.update') }}">
                        @csrf

                        <div class="mb-3">
                            <label>New Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <button class="btn btn-primary w-100">Update Password</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
