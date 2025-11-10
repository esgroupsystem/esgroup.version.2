@extends('layouts.landing')
@section('title', '500 Server Error')

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
                        <div class="fw-black lh-1 text-300 fs-error">500</div>

                        <p class="lead mt-4 text-800 font-sans-serif fw-semi-bold">
                            Whoops, something went wrong!
                        </p>

                        <hr>

                        <p>
                            Try refreshing the page or going back to retry.
                            If this continues, <a href="support@esgroup.com.ph">contact us</a>.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
@endsection
