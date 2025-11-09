@extends('layouts.app')
@section('title', '503 Service Unavailable')

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
                        <div class="fw-black lh-1 text-300 fs-error">503</div>

                        <p class="lead mt-4 text-800 font-sans-serif fw-semi-bold">
                            The service is temporarily unavailable.
                        </p>

                        <hr>

                        <p>
                            We are performing maintenance. Please try again later.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
@endsection
