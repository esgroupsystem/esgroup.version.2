@extends('layouts.app')
@section('title', $bus->body_number . ' CCTV Details')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">

            <div class="card mb-3">
                <div class="card-body py-3">
                    <div class="row flex-between-center g-3">
                        <div class="col-auto">
                            <div class="d-flex align-items-center">
                                <span class="fas fa-bus text-primary fs-4 me-3"></span>
                                <div>
                                    <h4 class="mb-0">{{ $bus->body_number }} CCTV Details</h4>
                                    <p class="fs-10 mb-0 text-600">{{ $bus->display_name }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-auto">
                            <a href="{{ route('concern.bus-status') }}" class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-arrow-left me-1"></span> Back to Bus Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <span class="fas fa-exclamation-circle text-danger me-2"></span>
                            <span class="fs-11 text-600 fw-bold">ACTIVE ISSUES</span>
                            <h3 class="mt-2 mb-0 text-danger">{{ $totalIssues }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <span class="fas fa-check-circle text-success me-2"></span>
                            <span class="fs-11 text-600 fw-bold">FIXED / CLOSED</span>
                            <h3 class="mt-2 mb-0 text-success">{{ $completedCount }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="fs-11 text-600 fw-bold">PLATE NUMBER</div>
                            <h6 class="mt-2 mb-0">{{ $bus->plate_number ?? '—' }}</h6>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="fs-11 text-600 fw-bold">GARAGE</div>
                            <h6 class="mt-2 mb-0">{{ $bus->garage ?? '—' }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-body-tertiary py-3">
                    <h6 class="mb-0">
                        <span class="fas fa-chart-bar text-primary me-2"></span>Issues Per Category
                    </h6>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($statusSummary as $label => $count)
                            @php
                                $percent = $totalIssues > 0 ? round(($count / $totalIssues) * 100) : 0;
                            @endphp

                            <div class="col-md">
                                <div class="issue-chart-box">
                                    <div class="d-flex justify-content-between mb-1">
                                        <strong>{{ $label }}</strong>
                                        <span
                                            class="badge rounded-pill {{ $count > 0 ? 'badge-subtle-danger' : 'badge-subtle-success' }}">
                                            {{ $count }}
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $count > 0 ? 'bg-danger' : 'bg-success' }}"
                                            style="width: {{ $percent }}%"></div>
                                    </div>
                                    <div class="fs-11 text-600 mt-1">{{ $percent }}%</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-body-tertiary py-3">
                    <form method="GET" action="{{ route('concern.bus-status.show', $bus->body_number) }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Filter Issue Type</label>
                                <select class="form-select form-select-sm" name="issue">
                                    <option value="">All Issues</option>
                                    @foreach ($issueOptions as $it)
                                        <option value="{{ $it }}" @selected($issue === $it)>
                                            {{ $it }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Filter Status</label>
                                <select class="form-select form-select-sm" name="status">
                                    <option value="">All Status</option>
                                    @foreach ($statusOptions as $st)
                                        <option value="{{ $st }}" @selected($status === $st)>
                                            {{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <button class="btn btn-primary btn-sm" type="submit">
                                    <span class="fas fa-filter me-1"></span> Apply Filter
                                </button>

                                <a href="{{ route('concern.bus-status.show', $bus->body_number) }}"
                                    class="btn btn-falcon-default btn-sm">
                                    Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="card mb-3">
                        <div class="card-header bg-body-tertiary py-2">
                            <h6 class="mb-0">
                                <span class="fas fa-exclamation-triangle text-warning me-2"></span>Active Job Orders
                            </h6>
                        </div>

                        <div class="card-body">
                            @forelse ($activeJobOrders as $concern)
                                @php
                                    $isOverdue =
                                        $concern->status === 'In Progress' &&
                                        $concern->created_at &&
                                        $concern->created_at->lt(now()->subDays(3));
                                @endphp

                                <div class="job-card {{ $isOverdue ? 'overdue-job' : '' }}">
                                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                        <div>
                                            <h6 class="mb-1 text-primary">{{ $concern->jo_no }}</h6>
                                            <span
                                                class="badge rounded-pill badge-subtle-secondary">{{ $concern->issue_type }}</span>
                                            <span
                                                class="badge rounded-pill {{ $concern->status === 'Open' ? 'badge-subtle-warning' : 'badge-subtle-info' }}">
                                                {{ $concern->status }}
                                            </span>

                                            @if ($isOverdue)
                                                <span class="badge rounded-pill badge-subtle-warning">
                                                    <span class="fas fa-clock me-1"></span> Overdue
                                                </span>
                                            @endif
                                        </div>

                                        <div class="fs-11 text-600">
                                            Assigned: <strong>{{ optional($concern->assignee)->full_name ?? '—' }}</strong>
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <div class="detail-box">
                                                <div class="detail-label">Problem</div>
                                                <div>{{ $concern->problem_details ?? '—' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="detail-box">
                                                <div class="detail-label">Action</div>
                                                <div>{{ $concern->action_taken ?? '—' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="detail-box">
                                                <div class="detail-label">Parts</div>
                                                @forelse ($concern->usedItems as $used)
                                                    <div class="fs-11">
                                                        {{ $used->inventoryItem->item_name ?? 'Item' }}
                                                        <strong>x{{ $used->qty_used }}</strong>
                                                    </div>
                                                @empty
                                                    —
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-600">No active job orders found.</div>
                            @endforelse

                            <div class="mt-3">
                                {{ $activeJobOrders->links('pagination.custom') }}
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-body-tertiary py-2">
                            <h6 class="mb-0">
                                <span class="fas fa-check-circle text-success me-2"></span>Fixed / Closed Job Orders
                            </h6>
                        </div>

                        <div class="card-body">
                            @forelse ($completedJobOrders as $concern)
                                <div class="job-card completed-job">
                                    <div class="d-flex gap-2 flex-wrap mb-2">
                                        <h6 class="mb-0 text-success">{{ $concern->jo_no }}</h6>
                                        <span
                                            class="badge rounded-pill badge-subtle-secondary">{{ $concern->issue_type }}</span>
                                        <span
                                            class="badge rounded-pill {{ $concern->status === 'Fixed' ? 'badge-subtle-success' : 'badge-subtle-secondary' }}">
                                            {{ $concern->status }}
                                        </span>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <div class="detail-box">
                                                <div class="detail-label">Problem</div>
                                                <div>{{ $concern->problem_details ?? '—' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="detail-box">
                                                <div class="detail-label">Action</div>
                                                <div>{{ $concern->action_taken ?? '—' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="detail-box">
                                                <div class="detail-label">Parts</div>
                                                @forelse ($concern->usedItems as $used)
                                                    <div class="fs-11">
                                                        {{ $used->inventoryItem->item_name ?? 'Item' }}
                                                        <strong>x{{ $used->qty_used }}</strong>
                                                    </div>
                                                @empty
                                                    —
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-600">No fixed or closed job orders found.</div>
                            @endforelse

                            <div class="mt-3">
                                {{ $completedJobOrders->links('pagination.custom') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card mb-3">
                        <div class="card-header bg-body-tertiary py-2">
                            <h6 class="mb-0">
                                <span class="fas fa-box-open text-primary me-2"></span>Overall Parts Used
                            </h6>
                        </div>

                        <div class="card-body">
                            @forelse ($partsSummary as $part)
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span class="text-truncate pe-2">{{ $part['name'] }}</span>
                                    <span class="badge rounded-pill badge-subtle-primary">
                                        x{{ $part['qty'] }} {{ $part['unit'] }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-center text-600 py-4">No parts used yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-body-tertiary py-2">
                            <h6 class="mb-0">
                                <span class="fas fa-history text-primary me-2"></span>Timeline / History
                            </h6>
                        </div>

                        <div class="card-body">
                            @forelse ($timeline as $item)
                                <div class="timeline-item">
                                    <div class="fw-bold">{{ $item->jo_no }}</div>
                                    <div class="fs-11 text-600">
                                        {{ $item->issue_type }} • {{ $item->status }}
                                    </div>
                                    <div class="fs-11 text-500">
                                        {{ optional($item->updated_at)->format('M d, Y h:i A') }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-600 py-4">No history found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('styles')
    <style>
        .issue-chart-box,
        .job-card,
        .detail-box {
            border: 1px solid #e3e6ed;
            border-radius: .75rem;
            background: #fff;
        }

        .issue-chart-box {
            padding: .85rem;
        }

        .job-card {
            padding: 1rem;
            margin-bottom: .75rem;
        }

        .job-card:last-child {
            margin-bottom: 0;
        }

        .overdue-job {
            border-color: rgba(255, 193, 7, .55);
            background: #fffdf3;
        }

        .completed-job {
            border-color: rgba(25, 135, 84, .2);
            background: #fbfffd;
        }

        .detail-box {
            padding: .75rem;
            height: 100%;
            background: #f9fafd;
            font-size: .78rem;
        }

        .detail-label {
            font-size: .68rem;
            font-weight: 700;
            color: #5e6e82;
            text-transform: uppercase;
            margin-bottom: .35rem;
        }

        .timeline-item {
            border-left: 3px solid #2c7be5;
            padding-left: .75rem;
            padding-bottom: 1rem;
            margin-bottom: .75rem;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
        }
    </style>
@endpush
