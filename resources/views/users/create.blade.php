@extends('layouts.app')
@section('title', 'Create User | Jell Group')

@section('content')
<div class="container" data-layout="container">
    <div class="content">

        <div class="card">
            <div class="card-header d-flex flex-between-center">
                <h5>Create User</h5>
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

                <form action="{{ route('authentication.users.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <input type="text" name="role" class="form-control" placeholder="Role name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Account Status</label>
                            <select name="account_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="deactivated">Deactivated</option>
                            </select>
                        </div>

                    </div>

                    <button class="btn btn-primary mt-4">Create User</button>

                </form>

            </div>
        </div>

    </div>
</div>
@endsection
