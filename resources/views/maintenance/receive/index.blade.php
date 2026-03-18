@extends('layouts.app')
@section('title', 'Receiving Records')

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
        <div class="card mb-3">
            <div class="card-header">
                <div class="row flex-between-center">
                    <div class="col-auto">
                        <h5 class="fs-9 mb-0">Receiving Records</h5>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('receivings.create') }}" class="btn btn-primary btn-sm">
                            <span class="fas fa-plus me-1"></span> New Receiving
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-3">
                <input id="liveSearch" class="form-control form-control-sm"
                    placeholder="Search receiving number or delivered by..."
                    value="{{ request('search') }}">
            </div>

            <div id="receivingTable">
                @include('maintenance.receive.table')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        let timer = null;
        const searchBox = document.getElementById("liveSearch");

        searchBox.addEventListener("keyup", function() {
            let value = this.value;

            clearTimeout(timer);
            timer = setTimeout(() => {
                fetch(`?search=${encodeURIComponent(value)}`, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(res => res.text())
                .then(html => {
                    document.getElementById("receivingTable").innerHTML = html;
                });
            }, 300);
        });

        document.addEventListener("click", function(e) {
            if (e.target.closest(".pagination a")) {
                e.preventDefault();

                fetch(e.target.closest(".pagination a").getAttribute("href"), {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(res => res.text())
                .then(html => {
                    document.getElementById("receivingTable").innerHTML = html;
                });
            }
        });
    });
</script>
@endpush