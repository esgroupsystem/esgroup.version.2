@extends('layouts.app')
@section('title', 'CCTV Job Orders - IT Department')

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

            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Header --}}
            <div class="card mb-3">
                <div class="card-body py-3">
                    <div class="row flex-between-center g-3">
                        <div class="col-12 col-lg-auto">
                            <div class="d-flex align-items-center">
                                <span class="fas fa-video text-primary fs-5 me-3"></span>
                                <div>
                                    <h5 class="mb-0">CCTV Job Orders</h5>
                                    <p class="fs-10 mb-0 text-600">Monitor, assign, and resolve CCTV concerns.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-auto">
                            <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                                <button class="btn btn-falcon-default btn-sm d-lg-none" type="button"
                                    data-bs-toggle="offcanvas" data-bs-target="#filterCanvas">
                                    <span class="fas fa-filter me-1"></span> Filter
                                </button>

                                <a class="btn btn-falcon-default btn-sm"
                                    href="{{ route('concern.export', array_merge(request()->query(), ['type' => 'print'])) }}"
                                    target="_blank">
                                    <span class="fas fa-print me-1"></span> Print
                                </a>

                                <a class="btn btn-falcon-success btn-sm"
                                    href="{{ route('concern.export', array_merge(request()->query(), ['type' => 'csv'])) }}">
                                    <span class="fas fa-file-export me-1"></span> Export
                                </a>

                                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal"
                                    data-bs-target="#createModal">
                                    <span class="fas fa-plus me-1"></span> New Job Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Monitoring --}}
            <div class="card mb-3">
                <div class="card-header bg-body-tertiary py-3">
                    <div class="row flex-between-center g-2">
                        <div class="col-auto">
                            <h6 class="mb-0">Monitoring Overview</h6>
                            <p class="fs-10 mb-0 text-600">Status and workload summary.</p>
                        </div>

                        <div class="col-auto">
                            <div class="d-flex flex-wrap gap-1">
                                @foreach ($statusOptions as $val => $label)
                                    <a class="btn btn-sm {{ request('status') === $val ? 'btn-primary' : 'btn-falcon-default' }}"
                                        href="{{ route('concern.cctv.index', array_merge(request()->except('page'), ['status' => $val ?: null])) }}">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-layer-group text-primary me-2"></span>
                                    <span class="fs-10 text-600 fw-semi-bold">Total</span>
                                </div>
                                <h4 class="mb-0 mt-2">{{ $totalOrders }}</h4>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-exclamation-circle text-warning me-2"></span>
                                    <span class="fs-10 text-600 fw-semi-bold">Open</span>
                                </div>
                                <h4 class="mb-0 mt-2">{{ $openCount }}</h4>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-spinner text-info me-2"></span>
                                    <span class="fs-10 text-600 fw-semi-bold">In Progress</span>
                                </div>
                                <h4 class="mb-0 mt-2">{{ $progressCount }}</h4>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex align-items-center">
                                    <span class="fas fa-check-circle text-success me-2"></span>
                                    <span class="fs-10 text-600 fw-semi-bold">Fixed / Closed</span>
                                </div>
                                <h4 class="mb-0 mt-2">{{ $fixedCount + $closedCount }}</h4>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="border rounded-3 px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-11 text-600 fw-bold">TOP ISSUE</span>
                                    <span class="badge badge-subtle-primary">{{ $topIssueCount }}</span>
                                </div>
                                <div class="fw-semi-bold text-truncate mt-1">{{ $topIssue ?? '—' }}</div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="border rounded-3 px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-11 text-600 fw-bold">MOST USED PART</span>
                                    <span class="badge badge-subtle-primary">{{ $topPartCount }}</span>
                                </div>
                                <div class="fw-semi-bold text-truncate mt-1">{{ $topPart ?? '—' }}</div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="border rounded-3 px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-11 text-600 fw-bold">TOP ASSIGNEE</span>
                                    <span class="badge badge-subtle-primary">{{ $topAssigneeCount }}</span>
                                </div>
                                <div class="fw-semi-bold text-truncate mt-1">{{ $topAssignee ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="card">
                <div class="card-header bg-body-tertiary py-3">
                    <div class="row flex-between-center g-2">
                        <div class="col-auto">
                            <h6 class="mb-0">Job Order List</h6>
                            <p class="fs-10 mb-0 text-600">Compact table view for faster checking.</p>
                        </div>

                        <div class="col-12 col-lg-auto">
                            <form method="GET" action="{{ route('concern.cctv.index') }}">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white">
                                        <span class="fa fa-search"></span>
                                    </span>
                                    <input class="form-control" name="q" type="search"
                                        value="{{ request('q') }}" placeholder="Search JO, bus, issue..." />
                                    <button class="btn btn-primary" type="submit">Search</button>
                                </div>
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-sm table-hover mb-0 overflow-hidden cctv-simple-table">
                            <thead class="bg-200">
                                <tr>
                                    <th class="ps-3">JO / Reporter</th>
                                    <th>Bus</th>
                                    <th>Issue</th>
                                    <th>Items Used</th>
                                    <th>Status</th>
                                    <th>Assignee</th>
                                    <th class="text-center pe-3">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($jobOrders as $jo)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-900">{{ $jo->jo_no }}</div>
                                            <div class="fs-11 text-600">{{ $jo->reported_by ?: 'No reporter' }}</div>
                                        </td>

                                        <td>
                                            <div class="text-primary fw-semi-bold bus-text">
                                                {{ $busDisplayMap[$jo->bus_no] ?? $jo->bus_no }}
                                            </div>
                                        </td>

                                        <td>
                                            <span class="badge rounded-pill badge-subtle-secondary">
                                                {{ $jo->issue_type }}
                                            </span>
                                        </td>

                                        <td>
                                            @forelse($jo->usedItems->take(2) as $used)
                                                <div class="fs-11 text-700 item-text">
                                                    {{ $used->inventoryItem->item_name ?? 'Item' }}
                                                    <span class="fw-bold">x{{ $used->qty_used }}</span>
                                                </div>
                                            @empty
                                                <span class="text-500">—</span>
                                            @endforelse

                                            @if ($jo->usedItems->count() > 2)
                                                <span class="badge badge-subtle-info mt-1">
                                                    +{{ $jo->usedItems->count() - 2 }} more
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <span
                                                class="badge rounded-pill {{ $statusClasses[$jo->status] ?? 'badge-subtle-primary' }}">
                                                {{ $jo->status }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="text-700 assignee-text">
                                                {{ optional($jo->assignee)->full_name ?? '—' }}
                                            </div>
                                        </td>

                                        <td class="text-center pe-3">
                                            <div class="dropdown font-sans-serif position-static">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                    type="button" data-bs-toggle="dropdown" data-boundary="window"
                                                    aria-haspopup="true" aria-expanded="false">
                                                    <span class="fas fa-ellipsis-h fs-10"></span>
                                                </button>

                                                <div class="dropdown-menu dropdown-menu-end border py-0">
                                                    <div class="py-2">
                                                        <button class="dropdown-item" type="button"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editModal{{ $jo->id }}">
                                                            <span class="fas fa-eye me-2"></span> View / Edit
                                                        </button>

                                                        <form action="{{ route('concern.cctv.destroy', $jo->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Delete this job order?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="dropdown-item text-danger" type="submit">
                                                                <span class="fas fa-trash me-2"></span> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <span class="fas fa-video text-300 fs-4 mb-3 d-block"></span>
                                            <h6 class="mb-1">No Job Orders Found</h6>
                                            <p class="text-600 mb-0">Try changing your filters or create a new job order.
                                            </p>
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
                                Showing {{ $jobOrders->firstItem() ?? 0 }} to {{ $jobOrders->lastItem() ?? 0 }} of
                                {{ $jobOrders->total() }} entries
                            </small>
                        </div>

                        <div class="col-auto">
                            {{ $jobOrders->links('pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mobile Filter --}}
            <div class="offcanvas offcanvas-end" tabindex="-1" id="filterCanvas" aria-labelledby="filterCanvasLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="filterCanvasLabel">Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>

                <div class="offcanvas-body">
                    <form method="GET" action="{{ route('concern.cctv.index') }}">
                        <div class="mb-3">
                            <label class="form-label mb-1">Status</label>
                            <select class="form-select" name="status">
                                @foreach ($statusOptions as $val => $label)
                                    <option value="{{ $val }}" @selected(request('status') === $val)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="q" value="{{ request('q') }}">

                        <button class="btn btn-primary w-100" type="submit">
                            <span class="fas fa-check me-1"></span> Apply Filter
                        </button>
                    </form>
                </div>
            </div>

            @include('it_department.concern.partials.create-modal')
            @include('it_department.concern.partials.edit-modals', ['jobOrders' => $jobOrders])

        </div>
    </div>
@endsection

@include('it_department.concern.partials.styles')
@include('it_department.concern.partials.scripts')
