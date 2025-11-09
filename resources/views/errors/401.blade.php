@extends('layouts.app')
@section('title', '401 Unauthorized')

@section('content')
<main class="main" id="top">
    <div class="container" data-layout="container">
        <script>
            const isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                document.querySelector('[data-layout]').classList.replace('container', 'container-fluid');
            }
        </script>

        <div class="row flex-center min-vh-100 py-6 text-center">
            <div class="col-sm-10 col-md-8 col-lg-6 col-xxl-5">

                <a class="d-flex flex-center mb-4" href="/">
                    <img class="me-2" src="{{ asset('assets/img/icons/spot-illustrations/falcon.png') }}" width="58">
                    <span class="font-sans-serif text-primary fw-bolder fs-4">falcon</span>
                </a>

                <div class="card">
                    <div class="card-body p-4 p-sm-5">
                        <div class="fw-black lh-1 text-300 fs-error">401</div>

                        <p class="lead mt-4 text-800 font-sans-serif fw-semi-bold">
                            You must be logged in to access this page.
                        </p>

                        <hr>

                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm mt-3">
                            <span class="fas fa-sign-in-alt me-2"></span> Login
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
@endsection
