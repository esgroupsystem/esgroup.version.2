@extends('layouts.app')
@section('title', 'Conductor Leaves')

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

            {{-- HEADER CARD --}}
            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);">
                </div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Conductor Leaves</h3>
                            <p class="text-muted">Manage leaves for conductors — HR will approve automatically. Use actions to
                                record offenses or terminate.</p>
                        </div>

                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="{{ route('conductor-leave.conductor.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Add Leave
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DASHBOARD CARDS --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Active</h6>
                                <div class="fs-4 fw-bold">{{ $counts['active'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">1st Offence</h6>
                                <div class="fs-4 fw-bold">{{ $counts['first'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-comment fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">2nd Offence</h6>
                                <div class="fs-4 fw-bold">{{ $counts['second'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-envelope fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Termination</h6>
                                <div class="fs-4 fw-bold">{{ $counts['termination'] ?? 0 }}</div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-user-times fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLE CARD --}}
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Conductor Leave Records</h6>
                </div>

                {{-- SEARCH --}}
                <div class="p-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input class="form-control form-control-sm search" placeholder="Search conductor leave...">
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="conductorLeaveTable"
                        data-list='{"valueNames":["employee","type","from","to","days","remaining"],"page":10,"pagination":true}'>
                        <div class="table-responsive scrollbar" style="overflow: visible !important;">
                            <table class="table table-hover table-striped fs-10 mb-0">
                                <thead class="bg-200 text-900">
                                    <tr>
                                        <th class="sort" data-sort="employee">Employee</th>
                                        <th class="sort" data-sort="type">Leave Type</th>
                                        <th class="sort" data-sort="from">From</th>
                                        <th class="sort" data-sort="to">To</th>
                                        <th class="sort" data-sort="days">No. of Days</th>
                                        <th class="sort" data-sort="remaining">Remaining</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody class="list">
                                    @forelse ($leaves as $leave)
                                        <tr>
                                            <td class="employee">
                                                <strong>{{ $leave->employee->full_name }}</strong><br>
                                                <span
                                                    class="text-muted">{{ $leave->employee->position?->title ?? '-' }}</span>
                                            </td>

                                            <td class="type">
                                                {{ $leave->leave_type }}
                                                <span class="ms-1" data-bs-toggle="tooltip"
                                                    title="{{ e($leave->reason ?? 'No reason provided') }}">
                                                    <i class="fas fa-exclamation-circle text-info"></i>
                                                </span>
                                            </td>

                                            <td class="from">
                                                {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                                            <td class="to">
                                                {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                            <td class="days">{{ $leave->days }} Days</td>

                                            <td class="remaining">{!! $leave->remaining_status !!}</td>

                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>

                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        {{-- Edit --}}
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('conductor-leave.conductor.edit', $leave->id) }}">
                                                                <i class="fas fa-edit me-2 text-primary"></i>Edit Leave
                                                            </a>

                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item action-open-modal" href="#"
                                                                data-id="{{ $leave->id }}" data-action="ready"
                                                                data-employee="{{ e($leave->employee->full_name) }}"
                                                                data-type="{{ e($leave->leave_type) }}">
                                                                <i class="fas fa-user-check me-2 text-success"></i> Ready
                                                                for Duty
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>

                                                        {{-- 1st Offense (opens modal) --}}
                                                        <li>
                                                            <a class="dropdown-item action-open-modal" href="#"
                                                                data-id="{{ $leave->id }}" data-action="first"
                                                                data-employee="{{ e($leave->employee->full_name) }}"
                                                                data-type="{{ e($leave->leave_type) }}">
                                                                <i class="fas fa-comment me-2 text-info"></i> 1st Offense -
                                                                Text
                                                            </a>
                                                        </li>

                                                        {{-- 2nd Offense --}}
                                                        <li>
                                                            <a class="dropdown-item action-open-modal" href="#"
                                                                data-id="{{ $leave->id }}" data-action="second"
                                                                data-employee="{{ e($leave->employee->full_name) }}"
                                                                data-type="{{ e($leave->leave_type) }}">
                                                                <i class="fas fa-envelope me-2 text-warning"></i> 2nd
                                                                Offense - Letter
                                                            </a>
                                                        </li>

                                                        {{-- Termination --}}
                                                        <li>
                                                            <a class="dropdown-item action-open-modal text-danger"
                                                                href="#" data-id="{{ $leave->id }}"
                                                                data-action="terminate"
                                                                data-employee="{{ e($leave->employee->full_name) }}"
                                                                data-type="{{ e($leave->leave_type) }}">
                                                                <i class="fas fa-user-times me-2"></i> Terminate - Final
                                                                Letter
                                                            </a>
                                                        </li>

                                                        {{-- Cancel immediately via modal too --}}
                                                        <li>
                                                            <a class="dropdown-item action-open-modal" href="#"
                                                                data-id="{{ $leave->id }}" data-action="cancel"
                                                                data-employee="{{ e($leave->employee->full_name) }}"
                                                                data-type="{{ e($leave->leave_type) }}">
                                                                <i class="fas fa-ban me-2"></i> Cancel Leave
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>

                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-3">No leave records found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- pagination (List.js adds pages) --}}
                        <div class="d-flex justify-content-center my-3">
                            <button class="btn btn-sm btn-falcon-default me-1" data-list-pagination="prev"><span
                                    class="fas fa-chevron-left"></span></button>
                            <ul class="pagination mb-0"></ul>
                            <button class="btn btn-sm btn-falcon-default ms-1" data-list-pagination="next"><span
                                    class="fas fa-chevron-right"></span></button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ACTION MODAL (Option A) --}}
    <div class="modal fade" id="leaveActionModal" tabindex="-1" aria-labelledby="leaveActionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="leaveActionForm" method="POST" action="">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="leaveActionModalLabel">Confirm Action</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="action_type" id="modal_action_type" value="">

                        <div class="mb-2">
                            <strong id="modal_employee_name"></strong>
                            <div class="small text-muted" id="modal_leave_type"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Note (optional)</label>
                            <textarea name="note" id="modal_note" class="form-control" rows="4"
                                placeholder="Add note, reason, or letter reference..."></textarea>
                        </div>

                        <div class="alert alert-warning small">
                            This will record the selected action and update the leave record. HR will manually send
                            messages/letters.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="modal_submit_btn">Confirm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // make container allow overflow so tooltips render
            document.querySelectorAll(".table-responsive").forEach(el => el.style.overflow = "visible");

            // List.js initialization
            const table = new List("conductorLeaveTable", {
                valueNames: ["employee", "type", "from", "to", "days", "remaining"],
                page: 10,
                pagination: true
            });

            // search
            const searchInput = document.querySelector(".search");
            if (searchInput) {
                searchInput.addEventListener("keyup", function() {
                    table.search(this.value);
                    setTimeout(initTooltips, 150);
                });
            }

            // bootstrap tooltips
            function initTooltips() {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    const inst = bootstrap.Tooltip.getInstance(el);
                    if (inst) inst.dispose();
                    bootstrap.Tooltip.getOrCreateInstance(el, {
                        container: 'body',
                        trigger: 'hover'
                    });
                });
            }

            initTooltips();

            // Action modal wiring (opens modal and populates form)
            const modalEl = document.getElementById('leaveActionModal');
            const modal = new bootstrap.Modal(modalEl);
            const modalForm = document.getElementById('leaveActionForm');

            document.querySelectorAll('.action-open-modal').forEach(a => {
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.dataset.id;
                    const action = this.dataset.action; // first | second | terminate | cancel
                    const employee = this.dataset.employee;
                    const type = this.dataset.type;

                    // set modal form action (POST to route conductor-leave.action)
                    modalForm.action = "{{ route('conductor-leave.conductor.action', ':id') }}".replace(
                        ':id', id);

                    // fill modal fields
                    document.getElementById('modal_action_type').value = action;
                    document.getElementById('modal_employee_name').innerText = employee;
                    document.getElementById('modal_leave_type').innerText = type;
                    document.getElementById('modal_note').value = '';

                    // update title & submit button text
                    let title = 'Confirm Action';
                    let btnText = 'Confirm';
                    if (action === 'first') {
                        title = 'Record 1st Offense';
                        btnText = 'Record 1st Offense';
                    }
                    if (action === 'second') {
                        title = 'Record 2nd Offense';
                        btnText = 'Record 2nd Offense';
                    }
                    if (action === 'terminate') {
                        title = 'Terminate Employee';
                        btnText = 'Terminate';
                    }
                    if (action === 'cancel') {
                        title = 'Cancel Leave';
                        btnText = 'Cancel Leave';
                    }
                    if (action === 'ready') {
                        title = 'Mark As Ready for Duty';
                        btnText = 'Set As Ready';
                    }

                    document.getElementById('leaveActionModalLabel').innerText = title;
                    document.getElementById('modal_submit_btn').innerText = btnText;

                    modal.show();
                });
            });

            // re-init tooltips after pagination clicks
            document.querySelectorAll("[data-list-pagination]").forEach(btn => {
                btn.addEventListener("click", () => setTimeout(initTooltips, 150));
            });

        });
    </script>

    <style>
        /* little visual polish */
        .card .bg-card {
            opacity: .06;
        }

        .reason-tip i {
            cursor: help;
        }

        .table th {
            vertical-align: middle;
        }
    </style>
@endpush
