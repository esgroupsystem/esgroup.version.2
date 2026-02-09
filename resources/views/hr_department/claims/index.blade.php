@extends('layouts.app')
@section('title', 'Claims - HR')

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
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ===================== MONITORING / OVERVIEW ===================== --}}
            @php
                // IMPORTANT:
                // In controller, pass $allClaims (collection) as "all claims under current filters"
                // and $claims as paginated list.

                $statsSource = $allClaims ?? collect(); // collection
                $statusCounts = $statsSource->groupBy('status')->map->count();

                $typeCounts = $statsSource->groupBy('claim_type')->map->count()->sortDesc();
                $topType = $typeCounts->keys()->first();
                $topTypeCount = $typeCounts->first() ?? 0;

                $employeeCounts = $statsSource
                    ->map(fn($x) => $x->employee->full_name ?? null)
                    ->filter()
                    ->groupBy(fn($n) => $n)
                    ->map->count()
                    ->sortDesc();

                $topEmployee = $employeeCounts->keys()->first();
                $topEmployeeCount = $employeeCounts->first() ?? 0;

                $pill = [
                    '' => 'All',
                    'Draft' => 'Draft',
                    'Ongoing' => 'Ongoing',
                    'Requested' => 'Requested',
                    'Released' => 'Released',
                    'Rejected' => 'Rejected',
                ];
            @endphp

            <div class="card monitor-card shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom border-200">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                        <div>
                            <h6 class="mb-0">Monitoring</h6>
                            <small class="text-muted">Quick overview for claims status and workload</small>
                        </div>

                        {{-- Quick filter pills --}}
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach ($pill as $val => $label)
                                <a class="btn btn-sm {{ request('status') === $val ? 'btn-primary' : 'btn-falcon-default' }}"
                                    href="{{ route('claims.index', array_merge(request()->all(), ['status' => $val ?: null])) }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        {{-- Status tiles --}}
                        @foreach (['Draft', 'Ongoing', 'Approved', 'Released'] as $tile)
                            @php
                                $badge = match ($tile) {
                                    'Draft' => 'badge-subtle-secondary',
                                    'Ongoing' => 'badge-subtle-info',
                                    'Approved' => 'badge-subtle-success',
                                    'Released' => 'badge-subtle-warning',
                                    default => 'badge-subtle-primary',
                                };
                                $hint = match ($tile) {
                                    'Draft' => 'Pending input',
                                    'Ongoing' => 'Submitted claims',
                                    'Approved' => 'Approved by SSS',
                                    'Released' => 'Fund released',
                                    default => '',
                                };
                            @endphp
                            <div class="col-6 col-md-3">
                                <div class="p-3 border monitor-tile h-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted fs-11">{{ $tile }}</div>
                                        <span class="badge rounded-pill {{ $badge }}">{{ $tile }}</span>
                                    </div>
                                    <div class="fs-4 fw-bold mt-1">{{ $statusCounts[$tile] ?? 0 }}</div>
                                    <div class="form-hint">{{ $hint }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            {{-- =================== END MONITORING =================== --}}

            <div class="row g-3">
                {{-- TABLE --}}
                <div class="col-xxl-9 col-xl-8">
                    <div class="card jo-card shadow-sm">

                        <div class="card-header bg-body-tertiary border-bottom border-200">
                            <div class="d-flex flex-column flex-lg-row gap-2 align-items-lg-center justify-content-between">
                                <div>
                                    <h5 class="mb-0">Claims</h5>
                                    <small class="text-muted">Track SSS / Maternity / Paternity claims per employee</small>
                                </div>

                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                    {{-- Search --}}
                                    <form method="GET" action="{{ route('claims.index') }}">
                                        <div class="input-group input-group-sm" style="width: 340px;">
                                            <span class="input-group-text bg-white border-300">
                                                <span class="fa fa-search fs-10"></span>
                                            </span>
                                            <input class="form-control shadow-none border-300" name="q" type="search"
                                                value="{{ request('q') }}" placeholder="Search employee / ref # ..." />
                                            <button class="btn btn-outline-secondary border-300" type="submit">
                                                Search
                                            </button>
                                        </div>
                                        <input type="hidden" name="status" value="{{ request('status') }}">
                                        <input type="hidden" name="claim_type" value="{{ request('claim_type') }}">
                                    </form>

                                    {{-- Mobile filter --}}
                                    <button class="btn btn-sm btn-falcon-default d-xl-none" type="button"
                                        data-bs-toggle="offcanvas" data-bs-target="#filterCanvas"
                                        aria-controls="filterCanvas">
                                        <span class="fas fa-filter"></span>
                                        <span class="ms-1">Filter</span>
                                    </button>

                                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal"
                                        data-bs-target="#createModal">
                                        <span class="fas fa-plus" data-fa-transform="shrink-3"></span>
                                        <span class="ms-1">New Claim</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive scrollbar jo-table-wrap">
                                <table class="table table-sm mb-0 fs-10 align-middle jo-table">
                                    <thead class="bg-body-tertiary border-bottom border-200">
                                        <tr>
                                            <th class="ps-3">Employee</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Reference #</th>
                                            <th>Notification</th>
                                            <th>Filed</th>
                                            <th>Approval</th>
                                            <th>Fund Request</th>
                                            <th>Amount</th>
                                            <th class="text-end pe-3" style="width: 160px;">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($claims as $c)
                                            <tr>
                                                <td class="ps-3 fw-semi-bold">{{ $c->employee?->full_name ?? '—' }}</td>
                                                <td class="fw-semi-bold">{{ $c->claim_type }}</td>
                                                <td>
                                                    @php
                                                        $cls = match ($c->status) {
                                                            'Draft' => 'badge-subtle-secondary',
                                                            'Ongoing' => 'badge-subtle-info',
                                                            'Requested' => 'badge-subtle-primary',
                                                            'Released' => 'badge-subtle-warning',
                                                            'Rejected' => 'badge-subtle-danger',
                                                            default => 'badge-subtle-primary',
                                                        };
                                                    @endphp
                                                    <span
                                                        class="badge rounded-pill {{ $cls }}">{{ $c->status }}</span>
                                                </td>
                                                <td class="text-muted">{{ $c->reference_no ?? '—' }}</td>
                                                <td class="text-muted">{{ $c->date_of_notification?->format('M d, Y') ?? '—' }}</td>
                                                <td class="text-muted">{{ $c->date_filed?->format('M d, Y') ?? '—' }}</td>
                                                <td class="text-muted">{{ $c->approval_date?->format('M d, Y') ?? '—' }}</td>
                                                <td class="text-muted">{{ $c->fund_request_date?->format('M d, Y') ?? '—' }}</td>
                                                <td class="text-muted">{{ $c->amount ?? '—' }}</td>

                                                <td class="text-end pe-3 jo-actions">
                                                    <button class="btn btn-sm btn-falcon-default" type="button"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal{{ $c->id }}"
                                                        title="View / Update">
                                                        <span class="fas fa-eye"></span>
                                                    </button>

                                                    <form action="{{ route('claims.destroy', $c->id) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('Delete this claim?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-falcon-danger" type="submit"
                                                            title="Delete">
                                                            <span class="fas fa-trash"></span>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>

                                            {{-- EDIT MODAL --}}
                                            <div class="modal fade" id="editModal{{ $c->id }}" tabindex="-1"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form method="POST"
                                                            action="{{ route('claims.update', $c->id) }}">
                                                            @csrf
                                                            @method('PUT')

                                                            <div class="modal-header">
                                                                <div>
                                                                    <h5 class="modal-title mb-0">Update Claim</h5>
                                                                    <div class="form-hint">Update dates, status and
                                                                        reference number.</div>
                                                                </div>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>

                                                            <div class="modal-body">
                                                                <div class="row g-3">

                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Employee</label>
                                                                        <select class="form-select" name="employee_id"
                                                                            required>
                                                                            <option value="">-- Select Employee --
                                                                            </option>
                                                                            @foreach ($employees as $emp)
                                                                                <option value="{{ $emp->id }}"
                                                                                    @selected($c->employee_id == $emp->id)>
                                                                                    {{ $emp->full_name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Claim Type</label>
                                                                        <select class="form-select" name="claim_type"
                                                                            required>
                                                                            @foreach (['SSS', 'MATERNITY', 'PATERNITY'] as $t)
                                                                                <option value="{{ $t }}"
                                                                                    @selected($c->claim_type === $t)>
                                                                                    {{ $t }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Status</label>
                                                                        <select class="form-select" name="status"
                                                                            required>
                                                                            @foreach (['Draft', 'Ongoing', 'Approved', 'Requested', 'Released', 'Rejected'] as $st)
                                                                                <option value="{{ $st }}"
                                                                                    @selected($c->status === $st)>
                                                                                    {{ $st }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-12">
                                                                        <div class="soft-divider"></div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Reference #</label>
                                                                        <input class="form-control" name="reference_no"
                                                                            value="{{ $c->reference_no }}"
                                                                            placeholder="SSS Reference #">
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Amount (optional)</label>
                                                                        <input class="form-control" type="number"
                                                                            step="0.01" name="amount"
                                                                            value="{{ $c->amount }}">
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Date of
                                                                            Notification</label>
                                                                        <input class="form-control" type="date"
                                                                            name="date_of_notification"
                                                                            value="{{ optional($c->date_of_notification)->format('Y-m-d') }}">
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Date Filed</label>
                                                                        <input class="form-control" type="date"
                                                                            name="date_filed"
                                                                            value="{{ optional($c->date_filed)->format('Y-m-d') }}">
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Approval Date</label>
                                                                        <input class="form-control" type="date"
                                                                            name="approval_date"
                                                                            value="{{ optional($c->approval_date)->format('Y-m-d') }}">
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Fund Request Date</label>
                                                                        <input class="form-control" type="date"
                                                                            name="fund_request_date"
                                                                            value="{{ optional($c->fund_request_date)->format('Y-m-d') }}">
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Fund Released
                                                                            Date</label>
                                                                        <input class="form-control" type="date"
                                                                            name="fund_released_date"
                                                                            value="{{ optional($c->fund_released_date)->format('Y-m-d') }}">
                                                                    </div>

                                                                    <div class="col-12">
                                                                        <label class="form-label">Remarks</label>
                                                                        <textarea class="form-control" name="remarks" rows="3">{{ $c->remarks }}</textarea>
                                                                    </div>

                                                                </div>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-falcon-default"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <span class="fas fa-save me-1"></span> Update
                                                                </button>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center">
                                                    <div class="empty-state">
                                                        <div class="icon">
                                                            <span class="fas fa-file-alt"></span>
                                                        </div>
                                                        <div class="fw-bold">No Claims Found</div>
                                                        <div class="text-muted fs-11">Try clearing filters or create a new
                                                            claim.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer bg-body-tertiary border-top border-200">
                            <div
                                class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
                                <small class="text-muted">
                                    Showing {{ $claims->firstItem() ?? 0 }} to {{ $claims->lastItem() ?? 0 }} of
                                    {{ $claims->total() }}
                                </small>
                                <div class="ms-md-auto">
                                    {{ $claims->links() }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- FILTER (Desktop) --}}
                <div class="col-xxl-3 col-xl-4 d-none d-xl-block">
                    <div class="card filter-card shadow-sm">
                        <div class="card-header bg-body-tertiary border-bottom border-200">
                            <h6 class="mb-0">Filter</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('claims.index') }}">
                                <div class="mb-3">
                                    <label class="form-label mb-1">Claim Type</label>
                                    <select class="form-select form-select-sm" name="claim_type">
                                        <option value="">All</option>
                                        @foreach (['SSS', 'MATERNITY', 'PATERNITY'] as $t)
                                            <option value="{{ $t }}" @selected(request('claim_type') === $t)>
                                                {{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label mb-1">Status</label>
                                    <select class="form-select form-select-sm" name="status">
                                        <option value="">All</option>
                                        @foreach (['Draft', 'Ongoing', 'Approved', 'Requested', 'Released', 'Rejected'] as $st)
                                            <option value="{{ $st }}" @selected(request('status') === $st)>
                                                {{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label mb-1">Employee</label>
                                    <select class="form-select form-select-sm" name="employee_id">
                                        <option value="">All</option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}" @selected((string) request('employee_id') === (string) $emp->id)>
                                                {{ $emp->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <input type="hidden" name="q" value="{{ request('q') }}">
                                <button class="btn btn-primary w-100" type="submit">
                                    <span class="fas fa-check me-1"></span> Apply
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

            {{-- FILTER (Mobile Offcanvas) --}}
            <div class="offcanvas offcanvas-end" tabindex="-1" id="filterCanvas" aria-labelledby="filterCanvasLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="filterCanvasLabel">Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body">
                    <form method="GET" action="{{ route('claims.index') }}">
                        <div class="mb-3">
                            <label class="form-label mb-1">Claim Type</label>
                            <select class="form-select" name="claim_type">
                                <option value="">All</option>
                                @foreach (['SSS', 'MATERNITY', 'PATERNITY'] as $t)
                                    <option value="{{ $t }}" @selected(request('claim_type') === $t)>{{ $t }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label mb-1">Status</label>
                            <select class="form-select" name="status">
                                <option value="">All</option>
                                @foreach (['Draft', 'Ongoing', 'Approved', 'Requested', 'Released', 'Rejected'] as $st)
                                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label mb-1">Employee</label>
                            <select class="form-select" name="employee_id">
                                <option value="">All</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}" @selected((string) request('employee_id') === (string) $emp->id)>
                                        {{ $emp->full_name }}
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

            {{-- CREATE MODAL --}}
            <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('claims.store') }}">
                            @csrf

                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title mb-0">Create Claim</h5>
                                    <div class="form-hint">Fill up the details then save.</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row g-3">

                                    {{-- Employee (searchable via Choices) --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Employee</label>
                                        <select class="form-select js-choice" name="employee_id" required>
                                            <option value="">-- Select Employee --</option>
                                            @foreach ($employees as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Claim Type</label>
                                        <select class="form-select" name="claim_type" required>
                                            @foreach (['SSS', 'MATERNITY', 'PATERNITY'] as $t)
                                                <option value="{{ $t }}">{{ $t }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status" required>
                                            @foreach (['Draft', 'Ongoing', 'Requested', 'Released', 'Rejected'] as $st)
                                                <option value="{{ $st }}" @selected($st === 'Draft')>
                                                    {{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <div class="soft-divider"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Reference #</label>
                                        <input class="form-control" name="reference_no" placeholder="SSS Reference #">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Amount (optional)</label>
                                        <input class="form-control" type="number" step="0.01" name="amount"
                                            placeholder="0.00">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Date of Notification</label>
                                        <input class="form-control" type="date" name="date_of_notification">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Date Filed</label>
                                        <input class="form-control" type="date" name="date_filed">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Approval Date</label>
                                        <input class="form-control" type="date" name="approval_date">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Fund Request Date</label>
                                        <input class="form-control" type="date" name="fund_request_date">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Fund Released Date</label>
                                        <input class="form-control" type="date" name="fund_released_date">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Remarks</label>
                                        <textarea class="form-control" name="remarks" rows="3" placeholder="Optional notes..."></textarea>
                                    </div>

                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-falcon-default"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <span class="fas fa-save me-1"></span> Save Claim
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>


        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener("shown.bs.modal", function(e) {
            if (!window.Choices) return;

            e.target.querySelectorAll(".js-choice").forEach(select => {
                if (select.dataset.choicesInit === "1" || select.choices) return;

                new Choices(select, {
                    searchEnabled: true,
                    placeholder: true,
                    allowHTML: true
                });

                select.dataset.choicesInit = "1";
            });
        });
    </script>
@endpush
@push('styles')
    <style>
        .jo-card {
            border-radius: 14px;
            overflow: hidden;
        }

        .jo-card .card-header {
            padding: 1rem 1.25rem;
        }

        .jo-card .card-footer {
            padding: .9rem 1.25rem;
        }

        .jo-table-wrap {
            max-height: 560px;
        }

        .jo-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .jo-table tbody tr:hover {
            background: rgba(0, 0, 0, .025);
        }

        .jo-actions .btn {
            padding: .32rem .55rem;
        }

        .soft-divider {
            border-top: 1px dashed rgba(0, 0, 0, .08);
            margin: .75rem 0;
        }

        .empty-state {
            padding: 2.5rem 1rem;
        }

        .empty-state .icon {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(0, 0, 0, .04);
            margin: 0 auto .75rem;
        }

        .filter-card {
            border-radius: 14px;
        }

        .modal .modal-content {
            border-radius: 14px;
        }

        .form-hint {
            font-size: .78rem;
            color: #6c757d;
        }

        .monitor-card {
            border-radius: 14px;
        }

        .monitor-tile {
            border-radius: 12px;
        }

        .mini-row {
            display: flex;
            justify-content: space-between;
            padding: .25rem 0;
        }

        .mini-row+.mini-row {
            border-top: 1px dashed rgba(0, 0, 0, .08);
        }
    </style>
@endpush
