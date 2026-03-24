@extends('layouts.app')
@section('title', 'Bus List')

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

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">

                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-bus text-primary me-2"></i>
                            Bus List
                        </h5>
                        <p class="text-muted fs-10 mb-0">
                            Browse buses and view maintenance history
                        </p>
                    </div>

                    <form method="GET" action="{{ route('buses.index') }}" style="max-width:420px;width:100%;">
                        <div class="input-group input-group-sm">

                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-primary"></i>
                            </span>

                            <input type="text" name="search" value="{{ $search }}" class="form-control"
                                placeholder="Search plate, body no, name, garage...">

                            @if ($search)
                                <a href="{{ route('buses.index') }}" class="btn btn-outline-secondary">
                                    Clear
                                </a>
                            @endif

                            <button class="btn btn-primary">
                                Search
                            </button>

                        </div>
                    </form>

                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0 bus-table">

                            <thead class="bg-200 text-900">
                                <tr>
                                    <th>Bus</th>
                                    <th>Garage</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($buses as $bus)
                                    @php
                                        $status = strtolower($bus->status ?? '');

                                        $statusClass = match ($status) {
                                            'active', 'available' => 'success',
                                            'maintenance', 'repair' => 'warning',
                                            'inactive' => 'secondary',
                                            default => 'info',
                                        };
                                    @endphp

                                    <tr onclick="window.location='{{ route('buses.show', $bus->id) }}'" class="bus-row">

                                        <td>

                                            <div class="d-flex align-items-center">

                                                <div class="bus-avatar me-2">
                                                    <i class="fas fa-bus"></i>
                                                </div>

                                                <div>

                                                    <div class="fw-semibold">
                                                        {{ $bus->plate_number ?? 'N/A' }}
                                                    </div>

                                                    <div class="text-muted fs-10">
                                                        Body No: {{ $bus->body_number ?? 'N/A' }}
                                                    </div>

                                                    <div class="text-muted fs-10">
                                                        {{ $bus->name ?? '' }}
                                                    </div>

                                                </div>

                                            </div>

                                        </td>

                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="fas fa-warehouse text-primary me-1"></i>
                                                {{ $bus->garage ?? 'N/A' }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }}">
                                                {{ $bus->status ?? 'N/A' }}
                                            </span>
                                        </td>

                                        <td class="text-center">

                                            <a href="{{ route('buses.show', $bus->id) }}"
                                                class="btn btn-falcon-info btn-sm" onclick="event.stopPropagation();">

                                                <i class="fas fa-tools me-1"></i>
                                                History

                                            </a>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No buses found
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>

                    </div>

                </div>

                @if ($buses->hasPages())
                    <div class="card-footer bg-light">
                        {{ $buses->links('pagination.custom') }}
                    </div>
                @endif

            </div>

        </div>
    </div>

    <style>
        .bus-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #2c7be5;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .table td {
            padding: 12px 16px;
        }

        .bus-row {
            cursor: pointer;
            transition: 0.15s ease;
        }

        .bus-row:hover {
            background: #f8fafd;
        }
    </style>

@endsection
