@extends('layouts.app')
@section('title', 'Edit User | Jell Group')

@section('content')
<div class="container" data-layout="container">
    <div class="content">

        <div class="card">
            <div class="card-header d-flex flex-between-center">
                <h5>Edit User</h5>
                <a href="{{ route('authentication.users.index') }}" class="btn btn-link btn-sm">Back</a>
            </div>

            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('authentication.users.update', $user->id) }}" method="POST">
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="{{ $user->full_name }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" value="{{ $user->username }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" name="role" value="{{ $user->role }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Account Status</label>
                            <select name="account_status" class="form-select">
                                <option value="active" @if($user->account_status=='active') selected @endif>Active</option>
                                <option value="deactivated" @if($user->account_status=='deactivated') selected @endif>Deactivated</option>
                            </select>
                        </div>

                    </div>

                    <button class="btn btn-primary mt-4">Save Changes</button>
                </form>

                <hr class="my-4">

                <h6>Reset Password</h6>
                <form action="{{ route('authentication.users.reset.password', $user->id) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="password" class="form-control" name="password" placeholder="New password" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-warning">Reset Password</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection
