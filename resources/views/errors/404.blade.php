@extends('layouts.landing')
@section('title', '404 Not Found')

@section('content')
<main class="main" id="top">
    <div class="container" data-layout="container">
        <script>
            const isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                const container = document.querySelector('[data-layout]');
                container.classList.replace('container', 'container-fluid');
            }
        </script>

        <div class="row flex-center min-vh-100 py-6 text-center">
            <div class="col-sm-10 col-md-8 col-lg-6 col-xxl-5">

                <a class="d-flex flex-center mb-4" href="/">
                    <img class="me-2" src="{{ asset('assets/img/icons/spot-illustrations/falcon.png') }}" width="58" alt="">
                    <span class="font-sans-serif text-primary fw-bolder fs-4">falcon</span>
                </a>

                <div class="card">
                    <div class="card-body p-4 p-sm-5">
                        <div class="fw-black lh-1 text-300 fs-error">404</div>
                        <p class="lead mt-4 text-800 font-sans-serif fw-semi-bold">
                            The page you're looking for cannot be found.
                        </p>

                        <hr>

                        <p>
                            Ensure the URL is correct or the page hasn't been moved.
                            If you believe this is an error, <a href="mailto:support@esgroup.com.ph">contact us</a>.
                        </p>

                        <a class="btn btn-primary btn-sm mt-3" href="/">
                            <span class="fas fa-home me-2"></span>
                            Take me home
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
@endsection
