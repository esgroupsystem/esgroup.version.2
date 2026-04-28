@extends('layouts.app')
@section('title', 'CCTV Bus Status')

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
                <div class="card-body py-3">
                    <div class="row flex-between-center g-3">
                        <div class="col-auto">
                            <div class="d-flex align-items-center">
                                <span class="fas fa-bus text-primary fs-5 me-3"></span>
                                <div>
                                    <h5 class="mb-0">CCTV Bus Status</h5>
                                    <p class="fs-10 mb-0 text-600">
                                        Shows active Open / In Progress CCTV concerns per bus.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-auto">
                            <a href="{{ route('concern.cctv.index') }}" class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-arrow-left me-1"></span> Back to Job Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-body-tertiary py-3">
                    <div class="row flex-between-center g-2">
                        <div class="col-auto">
                            <h6 class="mb-0">Bus Issue Monitoring</h6>
                            <p class="fs-10 mb-0 text-600">
                                Click a bus to open full CCTV history and details.
                            </p>
                        </div>

                        <div class="col-12 col-lg-auto">
                            <form method="GET" action="{{ route('concern.bus-status') }}">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white">
                                        <span class="fa fa-search"></span>
                                    </span>
                                    <input class="form-control" name="q" type="search" value="{{ $q }}"
                                        placeholder="Search bus, plate, name, garage..." />
                                    <button class="btn btn-primary" type="submit">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-sm table-hover mb-0 bus-status-table">
                            <thead class="bg-200">
                                <tr>
                                    <th class="ps-3">Buses</th>
                                    <th class="text-center">CCTV</th>
                                    <th class="text-center">DVR</th>
                                    <th class="text-center">Monitor</th>
                                    <th class="text-center">Power Supply</th>
                                    <th class="text-center">Other</th>
                                    <th class="text-center pe-3">Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($busStatuses as $bus)
                                    <tr>
                                        <td class="ps-3">
                                            <a href="{{ route('concern.bus-status.show', $bus->body_number) }}"
                                                class="text-decoration-none">
                                                <div class="fw-bold text-primary d-flex align-items-center gap-2">
                                                    <span>{{ $bus->body_number }}</span>

                                                    <span class="click-hint">
                                                        &lt; Click Here
                                                    </span>

                                                    @if ($bus->total_issues > 0)
                                                        <span class="badge rounded-pill badge-subtle-danger ms-1">
                                                            {{ $bus->total_issues }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="fs-11 text-600 bus-status-sub">
                                                    {{ $bus->plate_number }} - {{ $bus->name }} - {{ $bus->garage }}
                                                </div>
                                            </a>
                                        </td>

                                        @foreach (['CCTV', 'DVR', 'Monitor', 'Power Supply', 'Other'] as $column)
                                            @php
                                                $count = $bus->status_summary[$column] ?? 0;
                                            @endphp

                                            <td class="text-center">
                                                @if ($count > 0)
                                                    <span class="badge rounded-pill badge-subtle-danger">
                                                        {{ $count }} {{ Str::plural('issue', $count) }}
                                                    </span>
                                                @else
                                                    <span class="badge rounded-pill badge-subtle-success">
                                                        <span class="fas fa-check"></span>
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <td class="text-center pe-3">
                                            @if ($bus->total_issues > 0)
                                                <span class="badge rounded-pill badge-subtle-danger">
                                                    {{ $bus->total_issues }} active
                                                </span>
                                            @else
                                                <span class="badge rounded-pill badge-subtle-success">
                                                    Ready for Deployment
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <span class="fas fa-bus text-300 fs-4 mb-3 d-block"></span>
                                            <h6 class="mb-1">No buses found</h6>
                                            <p class="text-600 mb-0">Try changing your search.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-body-tertiary py-3">
                    <div class="row flex-between-center g-2">
                        <div class="col-auto">
                            <small class="text-600">
                                Showing {{ $busStatuses->firstItem() ?? 0 }} to {{ $busStatuses->lastItem() ?? 0 }} of
                                {{ $busStatuses->total() }} buses
                            </small>
                        </div>

                        <div class="col-auto">
                            {{ $busStatuses->links('pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bus-status-table {
            font-size: .83rem;
            min-width: 950px;
        }

        .bus-status-table thead th {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .02em;
            color: #5e6e82;
            padding-top: .75rem;
            padding-bottom: .75rem;
            white-space: nowrap;
        }

        .bus-status-table tbody td {
            padding-top: .75rem;
            padding-bottom: .75rem;
            vertical-align: middle;
        }

        .bus-status-sub {
            max-width: 420px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .click-hint {
            font-size: .62rem;
            font-weight: 700;
            color: #e63757;
            animation: clickHeartbeat 1.2s infinite;
            white-space: nowrap;
        }

        @keyframes clickHeartbeat {

            0%,
            100% {
                transform: scale(1);
                opacity: .65;
            }

            35% {
                transform: scale(1.15);
                opacity: 1;
            }

            65% {
                transform: scale(1);
                opacity: .85;
            }
        }

        .bus-status-table a:hover .click-hint {
            color: #2c7be5;
        }
    </style>
@endpush
