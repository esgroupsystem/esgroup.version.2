@php
    $statusOptions = $statusOptions ?? [
        'Active',
        'Active(Re-Entry)',
        'Suspended',
        'Inactive',
        'Terminated',
        'Terminated(due to AWOL)',
        'End of Contract',
        'Retrench',
        'Retired',
        'Resigned',
    ];

    $companies = collect($companies ?? []);
    $garages = collect($garages ?? []);

    $hasFilters = request()->hasAny(['search', 'status', 'company', 'garage', 'per_page']);
@endphp

@once
    <style>
        .employee-filter-wrapper {
            background: #fff;
        }

        .employee-filter-wrapper .form-label {
            font-size: .72rem;
            color: #5e6e82;
            margin-bottom: .25rem;
        }

        .employee-filter-wrapper .form-control,
        .employee-filter-wrapper .form-select,
        .employee-filter-wrapper .input-group-text {
            font-size: .78rem;
        }

        .employee-filter-wrapper .btn {
            font-size: .78rem;
        }

        .employee-filter-badge {
            font-size: .68rem;
            padding: .25rem .45rem;
        }

        .employee-filter-actions .btn {
            min-width: 38px;
        }

        @media (max-width: 991.98px) {
            .employee-filter-actions {
                width: 100%;
            }

            .employee-filter-actions .btn,
            .employee-filter-actions a {
                flex: 1;
            }
        }
    </style>
@endonce

<div class="card-body border-bottom py-3 employee-filter-wrapper">
    <form method="GET" action="{{ url()->current() }}" id="employeeDirectoryFilterForm">
        <div class="row g-2 align-items-end">

            {{-- Search --}}
            <div class="col-lg-4">
                <label class="form-label fw-semibold">
                    Search Employee
                </label>

                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body">
                        <span class="fas fa-search text-500"></span>
                    </span>

                    <input id="employeeDirectorySearch" name="search" type="search" class="form-control"
                        placeholder="Name, ID, email, phone, company..." value="{{ request('search') }}"
                        autocomplete="off">
                </div>
            </div>

            {{-- Status --}}
            <div class="col-md-6 col-lg-2">
                <label class="form-label fw-semibold">
                    Status
                </label>

                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>

                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Company --}}
            <div class="col-md-6 col-lg-2">
                <label class="form-label fw-semibold">
                    Company
                </label>

                <select name="company" class="form-select form-select-sm">
                    <option value="">All Company</option>

                    @foreach ($companies as $company)
                        <option value="{{ $company }}" @selected(request('company') === $company)>
                            {{ $company }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Garage --}}
            <div class="col-md-6 col-lg-2">
                <label class="form-label fw-semibold">
                    Garage
                </label>

                <select name="garage" class="form-select form-select-sm">
                    <option value="">All Garage</option>

                    @foreach ($garages as $garage)
                        <option value="{{ $garage }}" @selected(request('garage') === $garage)>
                            {{ $garage }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Per Page --}}
            <div class="col-md-3 col-lg-1">
                <label class="form-label fw-semibold">
                    Show
                </label>

                <select name="per_page" class="form-select form-select-sm">
                    @foreach ([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="col-md-9 col-lg-1">
                <label class="form-label fw-semibold d-none d-lg-block">
                    &nbsp;
                </label>

                <div class="d-flex gap-1 employee-filter-actions">
                    <button type="submit" class="btn btn-sm btn-falcon-primary" title="Search / Apply Filter">
                        <span class="fas fa-search"></span>
                    </button>

                    <a href="{{ url()->current() }}" class="btn btn-sm btn-falcon-default" title="Reset Filter">
                        <span class="fas fa-times"></span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Active Filters --}}
        @if ($hasFilters)
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                <small class="text-600">
                    Active filters:
                </small>

                @if (request('search'))
                    <span class="badge badge-subtle-primary employee-filter-badge">
                        <span class="fas fa-search me-1"></span>
                        Search: {{ request('search') }}
                    </span>
                @endif

                @if (request('status'))
                    <span class="badge badge-subtle-info employee-filter-badge">
                        <span class="fas fa-user-check me-1"></span>
                        Status: {{ request('status') }}
                    </span>
                @endif

                @if (request('company'))
                    <span class="badge badge-subtle-success employee-filter-badge">
                        <span class="fas fa-building me-1"></span>
                        Company: {{ request('company') }}
                    </span>
                @endif

                @if (request('garage'))
                    <span class="badge badge-subtle-warning employee-filter-badge">
                        <span class="fas fa-warehouse me-1"></span>
                        Garage: {{ request('garage') }}
                    </span>
                @endif

                @if (request('per_page'))
                    <span class="badge badge-subtle-secondary employee-filter-badge">
                        <span class="fas fa-list me-1"></span>
                        Show: {{ request('per_page') }}
                    </span>
                @endif
            </div>
        @endif
    </form>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('employeeDirectoryFilterForm');
            const searchInput = document.getElementById('employeeDirectorySearch');

            if (!form) {
                return;
            }

            /*
             * Important:
             * Do NOT auto-submit on every keyup.
             * Auto-submit causes full page reload while typing, which makes Falcon layout
             * temporarily look duplicated or broken.
             */

            if (searchInput) {
                searchInput.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();

                        if (form.requestSubmit) {
                            form.requestSubmit();
                        } else {
                            form.submit();
                        }
                    }
                });
            }

            form.addEventListener('submit', function() {
                const pageInput = form.querySelector('[name="page"]');

                if (pageInput) {
                    pageInput.remove();
                }
            });
        });
    </script>
@endonce
