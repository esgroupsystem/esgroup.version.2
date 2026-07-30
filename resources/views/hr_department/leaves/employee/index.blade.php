@extends('layouts.app')
@section('title', 'Employee Leaves')

@section('content')
    <div class="container" data-layout="container">
        <script>
            const isFluid = JSON.parse(localStorage.getItem('isFluid'));

            if (isFluid) {
                const container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content employee-leave-page">
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-start">
                        <span class="fas fa-circle-exclamation fs-4 me-3 mt-1"></span>
                        <div>
                            <h6 class="alert-heading mb-1">Action could not be completed</h6>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card mb-4 overflow-hidden">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);">
                </div>

                <div class="card-body position-relative">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-3">
                                <div class="employee-page-icon">
                                    <span class="fas fa-user-clock"></span>
                                </div>

                                <div>
                                    <h3 class="mb-1">Admin Employee Leave Monitoring</h3>
                                    <p class="text-700 mb-0">
                                        Track leave schedules, garage assignment, notices, duty status, proof images,
                                        and employee status changes.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 text-lg-end">
                            <a href="{{ route('employee-leave.employee.create') }}" class="btn btn-primary">
                                <span class="fas fa-plus me-1"></span>
                                Add Employee Leave
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
                            Picture proof is required when marking the 1st, 2nd, or Final Notice as sent.
                            Ready for Duty and Cancel Leave do not require a picture. Once the employee reaches the
                            2nd Notice, the leave record and employee status are automatically set to
                            <strong>Inactive</strong>.
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
                            <div class="small text-600 mt-2">Employees with first notice</div>
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
                                            Record the first warning and upload picture proof that it was sent.
                                        </p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="workflow-step border rounded-3 p-3 h-100">
                                        <div class="badge rounded-pill badge-subtle-warning text-warning mb-2">Step 2</div>
                                        <h6 class="mb-1">2nd Notice</h6>
                                        <p class="text-700 small mb-0">
                                            Upload proof and automatically change the employee status to Inactive.
                                        </p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="workflow-step border rounded-3 p-3 h-100">
                                        <div class="badge rounded-pill badge-subtle-danger text-danger mb-2">Step 3</div>
                                        <h6 class="mb-1">Final Notice</h6>
                                        <p class="text-700 small mb-0">
                                            Upload final proof and move the employee record to Terminated.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="small text-700">
                                <strong>Note:</strong> Use Ready for Duty only when the employee has returned and is
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
                            <h5 class="mb-0">Employee Leave Records</h5>
                            <p class="mb-0 text-600 small">
                                Complete list with garage, leave period, status, notice proof, and actions.
                            </p>
                        </div>

                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-search"></span>
                                </span>
                                <input id="liveSearch" class="form-control"
                                    placeholder="Search employee, garage, company, status, leave type..."
                                    value="{{ request('search') }}" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="employeeLeaveTable">
                        @include('hr_department.leaves.employee.table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="employeeLeaveActionModal" tabindex="-1" aria-labelledby="employeeLeaveActionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="employeeLeaveActionForm" method="POST" action="" enctype="multipart/form-data">
                @csrf

                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-body-tertiary">
                        <div>
                            <h5 class="modal-title" id="employeeLeaveActionModalLabel">Confirm Action</h5>
                            <p class="mb-0 small text-600" id="employeeActionSubtitle">
                                Review the selected action before saving.
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="action_type" id="employeeActionType" value="">

                        <div class="employee-modal-box mb-3">
                            <div class="fw-bold text-900" id="employeeActionEmployee"></div>
                            <div class="small text-600" id="employeeActionLeaveType"></div>
                            <div class="small text-600" id="employeeActionGarage"></div>
                        </div>

                        <div class="mb-3">
                            <label for="employeeActionNote" class="form-label fw-semi-bold">Action Note</label>
                            <textarea name="note" id="employeeActionNote" class="form-control" rows="4"
                                placeholder="Enter notice reference, HR note, or reason."></textarea>
                        </div>

                        <div class="mb-3 d-none" id="employeeProofContainer">
                            <label for="employeeProofImage" class="form-label fw-semi-bold">
                                Picture Proof
                                <span class="text-danger">*</span>
                            </label>

                            <input type="file" name="proof_image" id="employeeProofImage" class="form-control"
                                accept="image/jpeg,image/png,image/webp" disabled>

                            <div class="form-text">
                                Required for 1st, 2nd, and Final Notice. Maximum file size: 4 MB.
                            </div>

                            <div class="proof-preview-wrapper d-none mt-3" id="employeeProofPreviewWrapper">
                                <img src="" alt="Selected proof preview" id="employeeProofPreview"
                                    class="proof-preview-image">
                            </div>
                        </div>

                        <div class="alert alert-warning small mb-0" id="employeeActionWarning">
                            This will update the selected leave record.
                        </div>
                    </div>

                    <div class="modal-footer bg-body-tertiary">
                        <button type="button" class="btn btn-falcon-default" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary" id="employeeActionSubmit">
                            Confirm
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const bootEmployeeLeavePage = () => {
                if (window.__employeeLeavePageBooted) {
                    return;
                }

                const modalElement = document.getElementById('employeeLeaveActionModal');
                const form = document.getElementById('employeeLeaveActionForm');
                const tableContainer = document.getElementById('employeeLeaveTable');

                if (!modalElement || !form || !tableContainer) {
                    console.error(
                        'Employee leave page is missing the modal, form, or table container.'
                    );

                    return;
                }

                if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                    window.setTimeout(bootEmployeeLeavePage, 150);

                    return;
                }

                window.__employeeLeavePageBooted = true;

                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                const byId = (id) => document.getElementById(id);

                const fields = {
                    actionType: byId('employeeActionType'),
                    title: byId('employeeLeaveActionModalLabel'),
                    subtitle: byId('employeeActionSubtitle'),
                    employee: byId('employeeActionEmployee'),
                    leaveType: byId('employeeActionLeaveType'),
                    garage: byId('employeeActionGarage'),
                    note: byId('employeeActionNote'),
                    proofContainer: byId('employeeProofContainer'),
                    proofInput: byId('employeeProofImage'),
                    previewWrapper: byId('employeeProofPreviewWrapper'),
                    preview: byId('employeeProofPreview'),
                    submit: byId('employeeActionSubmit')
                };

                if (!fields.actionType || !fields.submit) {
                    console.error('Employee leave action fields are incomplete.');

                    return;
                }

                let warning = byId('employeeActionWarning');

                if (!warning) {
                    warning = document.createElement('div');
                    warning.id = 'employeeActionWarning';
                    warning.className = 'alert alert-warning small mb-0';
                    warning.textContent = 'This will update the selected leave record.';
                    modalElement.querySelector('.modal-body')?.appendChild(warning);
                }

                const actionConfig = {
                    first: {
                        title: 'Mark 1st Notice Sent',
                        subtitle: 'Record the first warning and upload picture proof.',
                        submit: 'Mark 1st Notice',
                        buttonClass: 'btn btn-info',
                        alertClass: 'alert alert-info small mb-0',
                        warning: 'The first warning will be recorded. The employee remains active or on leave.',
                        requiresProof: true
                    },
                    second: {
                        title: 'Mark 2nd Notice Sent',
                        subtitle: 'Record the second warning and set the employee to Inactive.',
                        submit: 'Mark 2nd Notice + Set Inactive',
                        buttonClass: 'btn btn-warning',
                        alertClass: 'alert alert-warning small mb-0',
                        warning: 'The leave record and employee record will automatically become Inactive.',
                        requiresProof: true
                    },
                    terminate: {
                        title: 'Mark Final Notice Sent',
                        subtitle: 'Record the final warning and terminate the employee record.',
                        submit: 'Mark Final Notice',
                        buttonClass: 'btn btn-danger',
                        alertClass: 'alert alert-danger small mb-0',
                        warning: 'The leave record and employee record will become Terminated.',
                        requiresProof: true
                    },
                    cancel: {
                        title: 'Cancel Leave',
                        subtitle: 'Cancel the leave and return the employee to Active.',
                        submit: 'Cancel Leave',
                        buttonClass: 'btn btn-secondary',
                        alertClass: 'alert alert-secondary small mb-0',
                        warning: 'No picture proof is required. The employee will return to Active.',
                        requiresProof: false
                    },
                    ready: {
                        title: 'Mark Ready for Duty',
                        subtitle: 'Complete the leave and return the employee to Active.',
                        submit: 'Set Ready for Duty',
                        buttonClass: 'btn btn-success',
                        alertClass: 'alert alert-success small mb-0',
                        warning: 'No picture proof is required. The leave will become Completed.',
                        requiresProof: false
                    }
                };

                let previewUrl = null;
                let searchTimer = null;

                const clearPreview = () => {
                    if (previewUrl) {
                        URL.revokeObjectURL(previewUrl);
                        previewUrl = null;
                    }

                    if (fields.preview) {
                        fields.preview.removeAttribute('src');
                    }

                    fields.previewWrapper?.classList.add('d-none');
                };

                const configureProof = (required) => {
                    if (!fields.proofContainer || !fields.proofInput) {
                        return;
                    }

                    fields.proofContainer.classList.toggle('d-none', !required);
                    fields.proofInput.disabled = !required;
                    fields.proofInput.required = required;

                    if (!required) {
                        fields.proofInput.value = '';
                        clearPreview();
                    }
                };

                const openActionModal = (button) => {
                    const action = button.dataset.action || '';
                    const url = button.dataset.url || '';
                    const config = actionConfig[action];

                    if (!config || !url) {
                        console.error('Invalid employee leave action button.', {
                            action,
                            url
                        });

                        return;
                    }

                    form.reset();
                    clearPreview();

                    form.setAttribute('action', url);
                    fields.actionType.value = action;

                    if (fields.employee) {
                        fields.employee.textContent =
                            button.dataset.employee || 'No employee name';
                    }

                    if (fields.leaveType) {
                        fields.leaveType.textContent =
                            button.dataset.type || 'No leave type';
                    }

                    if (fields.garage) {
                        fields.garage.textContent =
                            `Garage: ${button.dataset.garage || 'No garage assigned'}`;
                    }

                    if (fields.title) {
                        fields.title.textContent = config.title;
                    }

                    if (fields.subtitle) {
                        fields.subtitle.textContent = config.subtitle;
                    }

                    fields.submit.textContent = config.submit;
                    fields.submit.className = config.buttonClass;
                    fields.submit.disabled = false;

                    warning.className = config.alertClass;
                    warning.textContent = config.warning;

                    configureProof(config.requiresProof);
                    modal.show();
                };

                const initializeTooltips = () => {
                    if (!bootstrap.Tooltip) {
                        return;
                    }

                    document
                        .querySelectorAll('[data-bs-toggle="tooltip"]')
                        .forEach((element) => {
                            bootstrap.Tooltip.getInstance(element)?.dispose();

                            bootstrap.Tooltip.getOrCreateInstance(element, {
                                container: 'body',
                                trigger: 'hover'
                            });
                        });
                };

                const loadTable = async (url) => {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(
                            'Unable to refresh employee leave records.'
                        );
                    }

                    tableContainer.innerHTML = await response.text();
                    initializeTooltips();
                };

                document.addEventListener('click', (event) => {
                    const actionButton = event.target.closest(
                        '.employee-action-open-modal'
                    );

                    if (actionButton) {
                        event.preventDefault();

                        if (
                            actionButton.classList.contains('disabled') ||
                            actionButton.getAttribute('aria-disabled') === 'true'
                        ) {
                            return;
                        }

                        openActionModal(actionButton);

                        return;
                    }

                    const paginationLink = event.target.closest(
                        '#employeeLeaveTable .pagination a'
                    );

                    if (!paginationLink) {
                        return;
                    }

                    event.preventDefault();

                    loadTable(paginationLink.href).catch(() => {
                        window.location.href = paginationLink.href;
                    });
                });

                fields.proofInput?.addEventListener('change', () => {
                    clearPreview();

                    const file = fields.proofInput.files?.[0];

                    if (!file || !fields.preview) {
                        return;
                    }

                    previewUrl = URL.createObjectURL(file);
                    fields.preview.src = previewUrl;
                    fields.previewWrapper?.classList.remove('d-none');
                });

                form.addEventListener('submit', (event) => {
                    const actionUrl = form.getAttribute('action');

                    if (!actionUrl || !fields.actionType.value) {
                        event.preventDefault();
                        console.error(
                            'Employee leave form action is incomplete.'
                        );

                        return;
                    }

                    if (
                        fields.proofInput?.required &&
                        !fields.proofInput.files.length
                    ) {
                        event.preventDefault();
                        fields.proofInput.focus();
                        fields.proofInput.reportValidity();

                        return;
                    }

                    fields.submit.disabled = true;
                    fields.submit.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
                });

                modalElement.addEventListener('hidden.bs.modal', () => {
                    form.reset();
                    form.removeAttribute('action');
                    fields.actionType.value = '';

                    if (fields.proofInput) {
                        fields.proofInput.required = false;
                        fields.proofInput.disabled = true;
                    }

                    fields.proofContainer?.classList.add('d-none');
                    fields.submit.disabled = false;
                    fields.submit.textContent = 'Confirm';
                    fields.submit.className = 'btn btn-primary';
                    clearPreview();
                });

                const searchInput = document.getElementById('liveSearch');

                searchInput?.addEventListener('input', function() {
                    clearTimeout(searchTimer);

                    searchTimer = window.setTimeout(() => {
                        const url = new URL(window.location.href);
                        const searchValue = this.value.trim();

                        if (searchValue) {
                            url.searchParams.set('search', searchValue);
                        } else {
                            url.searchParams.delete('search');
                        }

                        url.searchParams.delete('page');

                        loadTable(url.toString()).catch(() => {
                            window.location.href = url.toString();
                        });
                    }, 300);
                });

                initializeTooltips();
            };

            if (document.readyState === 'loading') {
                document.addEventListener(
                    'DOMContentLoaded',
                    bootEmployeeLeavePage, {
                        once: true
                    }
                );
            } else {
                bootEmployeeLeavePage();
            }
        })();
    </script>
@endpush

@push('styles')
    <style>
        .employee-leave-page .employee-page-icon {
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 1rem;
            display: grid;
            place-items: center;
            background: var(--falcon-primary-bg-subtle, #e7f0ff);
            color: var(--falcon-primary, #2c7be5);
            font-size: 1.35rem;
        }

        .employee-leave-page .metric-card {
            border: 0;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, .05);
        }

        .employee-leave-page .metric-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: .75rem;
            display: grid;
            place-items: center;
            background: var(--falcon-gray-100, #f9fafd);
            font-size: 1.2rem;
        }

        .employee-leave-page .workflow-step {
            background: var(--falcon-gray-100, #f9fafd);
        }

        .employee-modal-box {
            background: var(--falcon-gray-100, #f9fafd);
            border: 1px solid var(--falcon-gray-200, #edf2f9);
            border-radius: .75rem;
            padding: 1rem;
        }

        .proof-preview-image {
            display: block;
            width: 100%;
            max-height: 240px;
            object-fit: contain;
            border: 1px solid var(--falcon-gray-300, #d8e2ef);
            border-radius: .75rem;
            background: var(--falcon-gray-100, #f9fafd);
            padding: .5rem;
        }

        .employee-leave-page .pagination {
            font-size: 14px !important;
        }

        .employee-leave-page .pagination .page-link {
            padding: 4px 10px !important;
            font-size: 14px !important;
            border-radius: 4px !important;
            color: #4a4a4a !important;
            border: 1px solid #d0d5dd !important;
            background: #f8f9fa !important;
        }

        .employee-leave-page .pagination .page-item.active .page-link {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
            font-weight: 600 !important;
        }

        .employee-leave-page .pagination .page-link:hover {
            background: #e2e6ea !important;
            border-color: #c4c9cf !important;
        }

        .employee-leave-page .pagination .page-item.disabled .page-link {
            opacity: .5 !important;
        }

        .employee-leave-page .pagination .page-item {
            margin: 0 2px !important;
        }
    </style>
@endpush
