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

        <div class="content conductor-leave-page">

            <div class="card mb-4 overflow-hidden">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);">
                </div>

                <div class="card-body position-relative">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-3">
                                <div class="conductor-page-icon">
                                    <span class="fas fa-user-clock"></span>
                                </div>

                                <div>
                                    <h3 class="mb-1">Conductor Leave Monitoring</h3>
                                    <p class="text-700 mb-0">
                                        Track leave schedules, garage assignment, notices, duty status, and automatic
                                        inactive records.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 text-lg-end">
                            <a href="{{ route('conductor-leave.conductor.create') }}" class="btn btn-primary">
                                <span class="fas fa-plus me-1"></span>
                                Add Conductor Leave
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm mb-4">
                <div class="d-flex">
                    <div class="me-3">
                        <span class="fas fa-circle-info fs-4"></span>
                    </div>
                    <div>
                        <h6 class="alert-heading mb-1">Process Rule</h6>
                        <p class="mb-0">
                            The table shows each conductor's assigned garage. Once the conductor reaches the 2nd Notice,
                            the leave record status and employee status are automatically set to <strong>Inactive</strong>.
                        </p>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card metric-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-700 fs-10 text-uppercase fw-semi-bold mb-1">Active / On Leave</p>
                                    <h3 class="mb-0">{{ number_format($counts['active'] ?? 0) }}</h3>
                                </div>
                                <div class="metric-icon text-primary">
                                    <span class="fas fa-user-check"></span>
                                </div>
                            </div>
                            <div class="small text-600 mt-2">Current active leave records</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card metric-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-700 fs-10 text-uppercase fw-semi-bold mb-1">1st Notice</p>
                                    <h3 class="mb-0">{{ number_format($counts['first'] ?? 0) }}</h3>
                                </div>
                                <div class="metric-icon text-info">
                                    <span class="fas fa-paper-plane"></span>
                                </div>
                            </div>
                            <div class="small text-600 mt-2">Conductors with first notice</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card metric-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-700 fs-10 text-uppercase fw-semi-bold mb-1">2nd Notice / Inactive</p>
                                    <h3 class="mb-0">{{ number_format($counts['second'] ?? 0) }}</h3>
                                </div>
                                <div class="metric-icon text-warning">
                                    <span class="fas fa-user-slash"></span>
                                </div>
                            </div>
                            <div class="small text-600 mt-2">Automatically set to Inactive</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card metric-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-700 fs-10 text-uppercase fw-semi-bold mb-1">Final / Terminated</p>
                                    <h3 class="mb-0">{{ number_format($counts['termination'] ?? 0) }}</h3>
                                </div>
                                <div class="metric-icon text-danger">
                                    <span class="fas fa-user-times"></span>
                                </div>
                            </div>
                            <div class="small text-600 mt-2">Final notice or terminated records</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-xl-5">
                    <div class="card h-100">
                        <div class="card-header bg-body-tertiary">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Garage Summary</h5>
                                <span class="badge badge-subtle-info text-info">Per Employee Garage</span>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive scrollbar">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="bg-200 text-900">
                                        <tr>
                                            <th>Garage</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">Active</th>
                                            <th class="text-end">2nd Notice</th>
                                            <th class="text-end">Inactive</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($garageSummary as $garage)
                                            <tr>
                                                <td class="fw-semi-bold">
                                                    <span class="fas fa-warehouse text-primary me-2"></span>
                                                    {{ $garage['garage'] }}
                                                </td>
                                                <td class="text-end">{{ number_format($garage['total']) }}</td>
                                                <td class="text-end">{{ number_format($garage['active']) }}</td>
                                                <td class="text-end">{{ number_format($garage['second_notice']) }}</td>
                                                <td class="text-end">
                                                    <span class="badge rounded-pill badge-subtle-warning text-warning">
                                                        {{ number_format($garage['inactive']) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    No garage summary available.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="card h-100">
                        <div class="card-header bg-body-tertiary">
                            <h5 class="mb-0">Notice Workflow</h5>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="workflow-step border rounded-3 p-3 h-100">
                                        <div class="badge rounded-pill badge-subtle-info text-info mb-2">Step 1</div>
                                        <h6 class="mb-1">1st Notice</h6>
                                        <p class="text-700 small mb-0">
                                            Used when conductor is past leave period and needs first warning.
                                        </p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="workflow-step border rounded-3 p-3 h-100">
                                        <div class="badge rounded-pill badge-subtle-warning text-warning mb-2">Step 2</div>
                                        <h6 class="mb-1">2nd Notice</h6>
                                        <p class="text-700 small mb-0">
                                            Automatically changes leave and employee status to Inactive.
                                        </p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="workflow-step border rounded-3 p-3 h-100">
                                        <div class="badge rounded-pill badge-subtle-danger text-danger mb-2">Step 3</div>
                                        <h6 class="mb-1">Final Notice</h6>
                                        <p class="text-700 small mb-0">
                                            Used for final notice and termination processing.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="small text-700">
                                <strong>Note:</strong> Use “Ready for Duty” only when the conductor has returned and is
                                cleared to work.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-body-tertiary">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-7">
                            <h5 class="mb-0">Conductor Leave Records</h5>
                            <p class="mb-0 text-600 small">
                                Complete list with garage, leave period, status, notice stage, and actions.
                            </p>
                        </div>

                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-search"></span>
                                </span>
                                <input id="liveSearch" class="form-control"
                                    placeholder="Search conductor, garage, company, status, leave type..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="conductorLeaveTable">
                        @include('hr_department.leaves.conductor.table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="leaveActionModal" tabindex="-1" aria-labelledby="leaveActionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="leaveActionForm" method="POST" action="">
                @csrf

                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-body-tertiary">
                        <div>
                            <h5 class="modal-title" id="leaveActionModalLabel">Confirm Action</h5>
                            <p class="mb-0 small text-600" id="modal_subtitle">Review the selected action before saving.
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="action_type" id="modal_action_type" value="">

                        <div class="conductor-modal-box mb-3">
                            <div class="fw-bold text-900" id="modal_employee_name"></div>
                            <div class="small text-600" id="modal_leave_type"></div>
                            <div class="small text-600" id="modal_garage"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semi-bold">Action Note</label>
                            <textarea name="note" id="modal_note" class="form-control" rows="4"
                                placeholder="Enter notice reference, HR note, or reason."></textarea>
                        </div>

                        <div class="alert alert-warning small mb-0" id="modal_warning">
                            This will update the selected leave record.
                        </div>
                    </div>

                    <div class="modal-footer bg-body-tertiary">
                        <button type="button" class="btn btn-falcon-default" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="modal_submit_btn">Confirm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            function initTooltips() {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    const instance = bootstrap.Tooltip.getInstance(el);

                    if (instance) {
                        instance.dispose();
                    }

                    bootstrap.Tooltip.getOrCreateInstance(el, {
                        container: 'body',
                        trigger: 'hover'
                    });
                });
            }

            function bindActionModal() {
                const modalElement = document.getElementById('leaveActionModal');
                const modal = new bootstrap.Modal(modalElement);
                const modalForm = document.getElementById('leaveActionForm');

                document.querySelectorAll('.action-open-modal').forEach(button => {
                    button.addEventListener('click', function(event) {
                        event.preventDefault();

                        if (this.classList.contains('disabled')) {
                            return;
                        }

                        const id = this.dataset.id;
                        const action = this.dataset.action;
                        const employee = this.dataset.employee || '';
                        const type = this.dataset.type || '';
                        const garage = this.dataset.garage || 'No garage assigned';

                        modalForm.action = "{{ route('conductor-leave.conductor.action', ':id') }}"
                            .replace(':id', id);

                        document.getElementById('modal_action_type').value = action;
                        document.getElementById('modal_employee_name').innerText = employee;
                        document.getElementById('modal_leave_type').innerText = type;
                        document.getElementById('modal_garage').innerText = `Garage: ${garage}`;
                        document.getElementById('modal_note').value = '';

                        const title = document.getElementById('leaveActionModalLabel');
                        const subtitle = document.getElementById('modal_subtitle');
                        const submit = document.getElementById('modal_submit_btn');
                        const warning = document.getElementById('modal_warning');

                        submit.className = 'btn btn-primary';

                        if (action === 'first') {
                            title.innerText = 'Mark 1st Notice Sent';
                            subtitle.innerText = 'This records the first warning notice.';
                            submit.innerText = 'Mark 1st Notice';
                            warning.className = 'alert alert-info small mb-0';
                            warning.innerText =
                                'This will update the notice tracker only. Employee status will not be set to Inactive yet.';
                        }

                        if (action === 'second') {
                            title.innerText = 'Mark 2nd Notice Sent';
                            subtitle.innerText =
                                'This will automatically deactivate the conductor record.';
                            submit.innerText = 'Mark 2nd Notice + Set Inactive';
                            submit.className = 'btn btn-warning';
                            warning.className = 'alert alert-warning small mb-0';
                            warning.innerText =
                                'Important: after confirming, the leave record status and employee status will automatically become Inactive.';
                        }

                        if (action === 'terminate') {
                            title.innerText = 'Mark Final Notice Sent';
                            subtitle.innerText = 'This will move the record to termination status.';
                            submit.innerText = 'Mark Final Notice';
                            submit.className = 'btn btn-danger';
                            warning.className = 'alert alert-danger small mb-0';
                            warning.innerText =
                                'This will mark the leave record and employee record as Terminated.';
                        }

                        if (action === 'cancel') {
                            title.innerText = 'Cancel Leave';
                            subtitle.innerText =
                                'This will cancel the leave and return the employee to Active.';
                            submit.innerText = 'Cancel Leave';
                            submit.className = 'btn btn-secondary';
                            warning.className = 'alert alert-secondary small mb-0';
                            warning.innerText =
                                'This will cancel the leave record and set the employee back to Active.';
                        }

                        if (action === 'ready') {
                            title.innerText = 'Mark Ready for Duty';
                            subtitle.innerText = 'This will complete the leave record.';
                            submit.innerText = 'Set Ready for Duty';
                            submit.className = 'btn btn-success';
                            warning.className = 'alert alert-success small mb-0';
                            warning.innerText =
                                'This will mark the leave as Completed and set the employee back to Active.';
                        }

                        modal.show();
                    });
                });
            }

            function refreshBindings() {
                initTooltips();
                bindActionModal();
            }

            refreshBindings();

            let timer = null;
            const input = document.getElementById("liveSearch");

            if (input) {
                input.addEventListener("keyup", function() {
                    const search = this.value;
                    clearTimeout(timer);

                    timer = setTimeout(() => {
                        fetch(`?search=${encodeURIComponent(search)}`, {
                                headers: {
                                    "X-Requested-With": "XMLHttpRequest"
                                }
                            })
                            .then(response => response.text())
                            .then(html => {
                                document.getElementById("conductorLeaveTable").innerHTML = html;
                                refreshBindings();
                            });
                    }, 300);
                });
            }

            document.addEventListener('click', function(event) {
                const link = event.target.closest('#conductorLeaveTable .pagination a');

                if (!link) {
                    return;
                }

                event.preventDefault();

                fetch(link.href, {
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById("conductorLeaveTable").innerHTML = html;
                        refreshBindings();
                    });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .conductor-leave-page .conductor-page-icon {
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 1rem;
            display: grid;
            place-items: center;
            background: var(--falcon-primary-bg-subtle, #e7f0ff);
            color: var(--falcon-primary, #2c7be5);
            font-size: 1.35rem;
        }

        .conductor-leave-page .metric-card {
            border: 0;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, .05);
        }

        .conductor-leave-page .metric-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: .75rem;
            display: grid;
            place-items: center;
            background: var(--falcon-gray-100, #f9fafd);
            font-size: 1.2rem;
        }

        .conductor-leave-page .workflow-step {
            background: var(--falcon-gray-100, #f9fafd);
        }

        .conductor-modal-box {
            background: var(--falcon-gray-100, #f9fafd);
            border: 1px solid var(--falcon-gray-200, #edf2f9);
            border-radius: .75rem;
            padding: 1rem;
        }

        .conductor-leave-page .pagination {
            font-size: 14px !important;
        }

        .conductor-leave-page .pagination .page-link {
            padding: 4px 10px !important;
            font-size: 14px !important;
            border-radius: 4px !important;
            color: #4a4a4a !important;
            border: 1px solid #d0d5dd !important;
            background: #f8f9fa !important;
        }

        .conductor-leave-page .pagination .page-item.active .page-link {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
            font-weight: 600 !important;
        }

        .conductor-leave-page .pagination .page-link:hover {
            background: #e2e6ea !important;
            border-color: #c4c9cf !important;
        }

        .conductor-leave-page .pagination .page-item.disabled .page-link {
            opacity: .5 !important;
        }

        .conductor-leave-page .pagination .page-item {
            margin: 0 2px !important;
        }
    </style>
@endpush
