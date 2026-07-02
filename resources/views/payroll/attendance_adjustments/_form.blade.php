@php
    $adjustment = $payrollAttendanceAdjustment ?? null;

    $selectedType = old('adjustment_type', $adjustment->adjustment_type ?? '');
    $isGlobalDisaster = $selectedType === \App\Models\PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER;

    $workDate = old('work_date', $adjustment?->work_date ? $adjustment->work_date->format('Y-m-d') : '');

    $dateFrom = old('date_from', $adjustment?->date_from ? $adjustment->date_from->format('Y-m-d') : $workDate);

    $dateTo = old('date_to', $adjustment?->date_to ? $adjustment->date_to->format('Y-m-d') : $workDate);

    $offsetSourceDate = old(
        'offset_source_date',
        $adjustment?->offset_source_date ? $adjustment->offset_source_date->format('Y-m-d') : '',
    );

    $isPaid = old('is_paid', $adjustment->is_paid ?? true);
    $ignoreLate = old('ignore_late', $adjustment->ignore_late ?? true);
    $ignoreUndertime = old('ignore_undertime', $adjustment->ignore_undertime ?? true);
@endphp

@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <div class="fw-semibold mb-1">
            <span class="fas fa-exclamation-circle me-1"></span>
            Please check the following:
        </div>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border shadow-none mb-3">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0">
                    <span class="fas fa-user me-2 text-primary"></span>
                    Employee and Adjustment Type
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Biometrics Employee</label>
                        <select name="employee_picker" id="employee_picker" class="form-select">
                            <option value="">Select employee</option>

                            @foreach ($people as $person)
                                <option value="{{ $person->biometric_employee_id }}"
                                    data-biometric-id="{{ $person->biometric_employee_id }}"
                                    data-employee-no="{{ $person->employee_no }}"
                                    data-employee-name="{{ $person->employee_name }}" @selected(old('biometric_employee_id', $adjustment->biometric_employee_id ?? '') == $person->biometric_employee_id)>
                                    {{ $person->employee_name }}
                                </option>
                            @endforeach
                        </select>

                        <input type="hidden" name="biometric_employee_id" id="biometric_employee_id"
                            value="{{ old('biometric_employee_id', $adjustment->biometric_employee_id ?? '') }}">

                        <input type="hidden" name="employee_no" id="employee_no"
                            value="{{ old('employee_no', $adjustment->employee_no ?? '') }}">

                        <input type="hidden" name="employee_name" id="employee_name"
                            value="{{ old('employee_name', $adjustment->employee_name ?? '') }}">

                        <input type="hidden" name="offset_proof_verified" id="offset_proof_verified" value="0">
                        <input type="hidden" name="offset_proof_time_in" id="offset_proof_time_in" value="">
                        <input type="hidden" name="offset_proof_time_out" id="offset_proof_time_out" value="">
                        <input type="hidden" name="offset_proof_total_minutes" id="offset_proof_total_minutes"
                            value="">

                        @error('biometric_employee_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                        <div id="employee_picker_help" class="fs-10 text-600 mt-1">
                            Required for individual adjustments. Automatically skipped for Typhoon / Disaster.
                        </div>

                        @error('employee_name')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Adjustment Type</label>
                        <select name="adjustment_type" id="adjustment_type" class="form-select" required>
                            <option value="">Select type</option>

                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" @selected($selectedType === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        @error('adjustment_type')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border shadow-none mb-3 adjustment-section" data-section="leave">
            <div class="card-header bg-success-subtle">
                <h6 class="mb-0 text-success">
                    <span class="fas fa-notes-medical me-2"></span>
                    Sick Leave / Medical Leave
                </h6>
            </div>

            <div class="card-body">
                <div class="alert alert-success-subtle border-0">
                    Leave adjustments only need a date range. No time in and time out required.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control"
                            value="{{ $dateFrom }}">

                        @error('date_from')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control"
                            value="{{ $dateTo }}">

                        @error('date_to')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border shadow-none mb-3 adjustment-section" data-section="single-date">
            <div class="card-header bg-info-subtle">
                <h6 class="mb-0 text-info">
                    <span class="fas fa-calendar-day me-2"></span>
                    Work Date / Transfer Date
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" id="work_date_label">Work Date</label>
                        <input type="date" name="work_date" id="work_date" class="form-control"
                            value="{{ $workDate }}">

                        @error('work_date')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border shadow-none mb-3 adjustment-section" data-section="disaster">
            <div class="card-header bg-danger-subtle">
                <h6 class="mb-0 text-danger">
                    <span class="fas fa-cloud-showers-heavy me-2"></span>
                    Typhoon / Disaster Adjustment for All Employees
                </h6>
            </div>

            <div class="card-body">
                <div class="alert alert-danger-subtle border-0 mb-0">
                    This adjustment does not require employee selection. The system will pay a whole day only for
                    employees who have at least one biometric time-in on the selected work date. Employees with no
                    time-in on that date will not be paid by this adjustment.
                </div>
            </div>
        </div>

        <div class="card border shadow-none mb-3 adjustment-section" data-section="manual-time">
            <div class="card-header bg-primary-subtle">
                <h6 class="mb-0 text-primary">
                    <span class="fas fa-clock me-2"></span>
                    Manual Time In / Time Out
                </h6>
            </div>

            <div class="card-body">
                <div class="alert alert-primary-subtle border-0">
                    Use this for Change Schedule and Official Business.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Adjusted Time In</label>
                        <input type="time" name="adjusted_time_in" id="adjusted_time_in" class="form-control"
                            value="{{ old('adjusted_time_in', $adjustment->adjusted_time_in ?? '') }}">

                        @error('adjusted_time_in')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Adjusted Time Out</label>
                        <input type="time" name="adjusted_time_out" id="adjusted_time_out" class="form-control"
                            value="{{ old('adjusted_time_out', $adjustment->adjusted_time_out ?? '') }}">

                        @error('adjusted_time_out')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border shadow-none mb-3 adjustment-section" data-section="offset">
            <div class="card-header bg-warning-subtle">
                <h6 class="mb-0 text-warning">
                    <span class="fas fa-exchange-alt me-2"></span>
                    Offset Proof from Biometrics
                </h6>
            </div>

            <div class="card-body">
                <div class="alert alert-warning-subtle border-0">
                    Select the original date where the employee actually timed in/out.
                    The system will check biometrics logs as proof before saving.
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Biometric Proof Date</label>
                        <input type="date" name="offset_source_date" id="offset_source_date" class="form-control"
                            value="{{ $offsetSourceDate }}">

                        @error('offset_source_date')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <button type="button" id="check_offset_proof_btn" class="btn btn-falcon-warning w-100"
                            data-url="{{ route('payroll-attendance-adjustments.offset-proof') }}">
                            <span class="fas fa-search me-1"></span>
                            Check Biometrics Proof
                        </button>
                    </div>
                </div>

                <div id="offset_proof_result" class="mt-3 d-none"></div>
            </div>
        </div>

        <div class="card border shadow-none">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0">
                    <span class="fas fa-comment-alt me-2 text-primary"></span>
                    Reason and Remarks
                </h6>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reason</label>
                    <textarea name="reason" rows="3" class="form-control"
                        placeholder="Example: Typhoon Egay early dismissal / Employee submitted approved OB form / Medical certificate / Offset request.">{{ old('reason', $adjustment->reason ?? '') }}</textarea>

                    @error('reason')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label class="form-label fw-semibold">Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control" placeholder="Optional payroll notes.">{{ old('remarks', $adjustment->remarks ?? '') }}</textarea>

                    @error('remarks')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border shadow-none sticky-top" style="top: 80px;">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0">
                    <span class="fas fa-sliders-h me-2 text-primary"></span>
                    Payroll Effect
                </h6>
            </div>

            <div class="card-body">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_paid" id="is_paid" value="1"
                        {{ $isPaid ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_paid">
                        Paid Adjustment
                    </label>
                    <div class="fs-10 text-600">
                        Include this adjustment as payable in payroll computation.
                    </div>
                </div>

                <hr>

                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="ignore_late" id="ignore_late"
                        value="1" {{ $ignoreLate ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="ignore_late">
                        Ignore Late
                    </label>
                    <div class="fs-10 text-600">
                        Late deduction will not apply for the adjusted date.
                    </div>
                </div>

                <hr>

                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="ignore_undertime" id="ignore_undertime"
                        value="1" {{ $ignoreUndertime ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="ignore_undertime">
                        Ignore Undertime
                    </label>
                    <div class="fs-10 text-600">
                        Undertime deduction will not apply for the adjusted date.
                    </div>
                </div>

                <div class="alert alert-info-subtle border-0 mt-3 mb-0 fs-10">
                    <strong>Guide:</strong><br>
                    Sick Leave and Medical Leave do not need time.<br>
                    Offset requires biometric proof.<br>
                    OB and Change Schedule need manual time in/out.<br>
                    Typhoon / Disaster pays all employees with time-in as whole day.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Biometrics Proof Modal --}}
<div class="modal fade" id="biometricsProofModal" tabindex="-1" aria-labelledby="biometricsProofModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning-subtle border-bottom">
                <div>
                    <h5 class="modal-title text-warning mb-0" id="biometricsProofModalLabel">
                        <span class="fas fa-fingerprint me-2"></span>
                        Biometrics Proof Details
                    </h5>
                    <div class="fs-10 text-600 mt-1" id="biometricsProofModalSubtitle">
                        Checking employee biometrics logs.
                    </div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="biometricsProofModalBody">
                <div class="text-center py-5">
                    <span class="fas fa-spinner fa-spin fa-2x text-warning mb-3"></span>
                    <div class="fw-semibold">Checking biometrics logs...</div>
                    <div class="text-600 fs-10">
                        Please wait while the system checks the selected date.
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-body-tertiary">
                <button type="button" class="btn btn-falcon-default" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

        function el(id) {
            return document.getElementById(id);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function normalizeAmPm(value) {
            return String(value ?? '')
                .replace(/\s+/g, '')
                .replace(/am$/i, 'AM')
                .replace(/pm$/i, 'PM');
        }

        function parseTimeToMinutes(value) {
            if (!value) {
                return null;
            }

            let rawValue = String(value).trim();

            if (rawValue.includes('T')) {
                rawValue = rawValue.split('T')[1] || rawValue;
            }

            if (rawValue.includes(' ')) {
                rawValue = rawValue.split(' ')[1] || rawValue;
            }

            rawValue = rawValue.replace(/\s+/g, '');

            const match = rawValue.match(/^(\d{1,2}):(\d{2})(?::\d{2})?(AM|PM)?$/i);

            if (!match) {
                return null;
            }

            let hours = parseInt(match[1], 10);
            const minutes = parseInt(match[2], 10);
            const meridiem = match[3] ? match[3].toUpperCase() : null;

            if (Number.isNaN(hours) || Number.isNaN(minutes)) {
                return null;
            }

            if (meridiem === 'AM' && hours === 12) {
                hours = 0;
            }

            if (meridiem === 'PM' && hours < 12) {
                hours += 12;
            }

            return (hours * 60) + minutes;
        }

        function formatTimeToAmPm(value) {
            if (!value) {
                return 'N/A';
            }

            const rawValue = String(value).trim();

            if (/(\d{1,2}):(\d{2})(?::\d{2})?\s*(AM|PM)$/i.test(rawValue)) {
                return normalizeAmPm(rawValue.replace(/:\d{2}(?=\s*(AM|PM)$)/i, ''));
            }

            let timePart = rawValue;

            if (timePart.includes('T')) {
                timePart = timePart.split('T')[1] || timePart;
            }

            if (timePart.includes(' ')) {
                timePart = timePart.split(' ')[1] || timePart;
            }

            const pieces = timePart.split(':');

            if (pieces.length < 2) {
                return rawValue;
            }

            let hours = parseInt(pieces[0], 10);
            const minutes = pieces[1];

            if (Number.isNaN(hours)) {
                return rawValue;
            }

            const suffix = hours >= 12 ? 'PM' : 'AM';

            hours = hours % 12;

            if (hours === 0) {
                hours = 12;
            }

            return `${hours}:${minutes}${suffix}`;
        }

        function formatCheckTimeLabel(value) {
            if (!value) {
                return 'N/A';
            }

            const rawValue = String(value).trim();

            if (rawValue.includes('T')) {
                const parts = rawValue.split('T');
                return `${parts[0]} ${formatTimeToAmPm(parts[1])}`;
            }

            const parts = rawValue.split(' ');

            if (parts.length < 2) {
                return formatTimeToAmPm(rawValue);
            }

            return `${parts[0]} ${formatTimeToAmPm(parts[1])}`;
        }

        function calculateAccumulatedHoursLabel(timeIn, timeOut) {
            const startMinutes = parseTimeToMinutes(timeIn);
            let endMinutes = parseTimeToMinutes(timeOut);

            if (startMinutes === null || endMinutes === null) {
                return 'N/A';
            }

            if (endMinutes < startMinutes) {
                endMinutes += 1440;
            }

            const totalMinutes = endMinutes - startMinutes;
            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;
            const parts = [];

            if (hours > 0) {
                parts.push(`${hours} hr${hours > 1 ? 's' : ''}`);
            }

            if (minutes > 0) {
                parts.push(`${minutes} min${minutes > 1 ? 's' : ''}`);
            }

            return parts.length ? parts.join(' ') : '0 min';
        }

        function openBiometricsModal(title, subtitle, bodyHtml) {
            const modalElement = el('biometricsProofModal');
            const modalTitle = el('biometricsProofModalLabel');
            const modalSubtitle = el('biometricsProofModalSubtitle');
            const modalBody = el('biometricsProofModalBody');

            if (!modalElement || !modalTitle || !modalSubtitle || !modalBody) {
                alert('Biometrics proof modal is missing in the Blade file.');
                return;
            }

            modalTitle.innerHTML = title;
            modalSubtitle.innerHTML = subtitle;
            modalBody.innerHTML = bodyHtml;

            if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                alert('Bootstrap modal JavaScript is not loaded.');
                return;
            }

            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        }

        function openLoadingModal(employeeName, proofDate) {
            openBiometricsModal(
                '<span class="fas fa-fingerprint me-2"></span> Biometrics Proof Details',
                `Employee: <strong>${escapeHtml(employeeName || 'N/A')}</strong> | Date: <strong>${escapeHtml(proofDate || 'N/A')}</strong>`,
                `
                    <div class="text-center py-5">
                        <span class="fas fa-spinner fa-spin fa-2x text-warning mb-3"></span>
                        <div class="fw-semibold">Checking biometrics logs...</div>
                        <div class="text-600 fs-10">
                            Please wait while the system checks the selected employee and proof date.
                        </div>
                    </div>
                `
            );
        }

        function openErrorModal(message, details = '') {
            openBiometricsModal(
                '<span class="fas fa-exclamation-triangle me-2"></span> Biometrics Proof Check Failed',
                'The system could not verify the selected biometrics proof.',
                `
                    <div class="alert alert-danger border-0 shadow-sm mb-0">
                        <div class="fw-semibold mb-1">
                            <span class="fas fa-times-circle me-1"></span>
                            ${escapeHtml(message)}
                        </div>
                        ${details ? `<div class="fs-10 mt-2">${details}</div>` : ''}
                    </div>
                `
            );
        }

        function openNoProofModal(data, employeeName, proofDate) {
            openBiometricsModal(
                '<span class="fas fa-search me-2"></span> No Biometrics Proof Found',
                `Employee: <strong>${escapeHtml(employeeName)}</strong> | Date: <strong>${escapeHtml(proofDate)}</strong>`,
                `
                    <div class="alert alert-warning border-0 shadow-sm mb-3">
                        <div class="fw-semibold mb-1">
                            <span class="fas fa-info-circle me-1"></span>
                            No biometrics logs found
                        </div>
                        ${escapeHtml(data.message || 'No biometrics logs were found for this employee on the selected date.')}
                    </div>

                    <div class="card border shadow-none">
                        <div class="card-header bg-body-tertiary">
                            <h6 class="mb-0">
                                <span class="fas fa-user me-2 text-warning"></span>
                                Checked Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="fs-10 text-600">Employee</div>
                                    <div class="fw-semibold">${escapeHtml(employeeName)}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fs-10 text-600">Proof Date</div>
                                    <div class="fw-semibold">${escapeHtml(proofDate)}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `
            );
        }

        function renderLogsRows(logs) {
            if (!Array.isArray(logs) || logs.length === 0) {
                return `
                    <tr>
                        <td colspan="6" class="text-center text-600 py-4">
                            No individual log rows returned.
                        </td>
                    </tr>
                `;
            }

            return logs.map(function(log, index) {
                return `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(log.check_time_label || formatCheckTimeLabel(log.check_time) || 'N/A')}</td>
                        <td>${escapeHtml(log.employee_name || 'N/A')}</td>
                        <td>${escapeHtml(log.employee_no || log.employee_id || log.crosschex_id || 'N/A')}</td>
                        <td>${escapeHtml(log.state || 'N/A')}</td>
                        <td>${escapeHtml(log.device_name || 'N/A')}</td>
                    </tr>
                `;
            }).join('');
        }

        function setOffsetProofHiddenFields(proof) {
            const verifiedInput = el('offset_proof_verified');
            const timeInInput = el('offset_proof_time_in');
            const timeOutInput = el('offset_proof_time_out');
            const totalMinutesInput = el('offset_proof_total_minutes');

            if (verifiedInput) {
                verifiedInput.value = '1';
            }

            if (timeInInput) {
                timeInInput.value = proof.time_in || '';
            }

            if (timeOutInput) {
                timeOutInput.value = proof.time_out || '';
            }

            if (totalMinutesInput) {
                totalMinutesInput.value = proof.accumulated_minutes || '';
            }
        }

        function resetOffsetProofHiddenFields() {
            const verifiedInput = el('offset_proof_verified');
            const timeInInput = el('offset_proof_time_in');
            const timeOutInput = el('offset_proof_time_out');
            const totalMinutesInput = el('offset_proof_total_minutes');

            if (verifiedInput) {
                verifiedInput.value = '0';
            }

            if (timeInInput) {
                timeInInput.value = '';
            }

            if (timeOutInput) {
                timeOutInput.value = '';
            }

            if (totalMinutesInput) {
                totalMinutesInput.value = '';
            }
        }

        function openProofModal(proof, employeeName, proofDate) {
            const logs = Array.isArray(proof.logs) ? proof.logs : [];

            const timeInLabel = proof.time_in_label || formatTimeToAmPm(proof.time_in);
            const timeOutLabel = proof.time_out_label || formatTimeToAmPm(proof.time_out);

            const timeRangeLabel = proof.time_range_label ||
                `${timeInLabel} - ${timeOutLabel}`;

            const accumulatedHoursLabel = proof.accumulated_hours_label ||
                calculateAccumulatedHoursLabel(proof.time_in, proof.time_out);

            setOffsetProofHiddenFields(proof);

            openBiometricsModal(
                '<span class="fas fa-check-circle me-2"></span> Biometrics Proof Found',
                `Employee: <strong>${escapeHtml(proof.employee_name || employeeName)}</strong> | Date: <strong>${escapeHtml(proof.date || proofDate)}</strong>`,
                `
                    <div class="alert alert-success border-0 shadow-sm">
                        <div class="fw-semibold">
                            <span class="fas fa-check-circle me-1"></span>
                            Biometrics logs were found for this employee on the selected date.
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="fs-10 text-600">Employee</div>
                                    <div class="fw-bold text-900">
                                        ${escapeHtml(proof.employee_name || employeeName)}
                                    </div>
                                    <div class="fs-10 text-600 mt-1">
                                        Emp No: ${escapeHtml(proof.employee_no || 'N/A')}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="fs-10 text-600">Proof Date</div>
                                    <div class="fw-bold text-900">
                                        ${escapeHtml(proof.date || proofDate)}
                                    </div>
                                    <div class="fs-10 text-600 mt-1">
                                        Source date
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="fs-10 text-600">Time Range</div>
                                    <div class="fw-bold text-primary">
                                        ${escapeHtml(timeRangeLabel)}
                                    </div>
                                    <div class="fs-10 text-600 mt-1">
                                        First to last log
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="fs-10 text-600">Accumulated</div>
                                    <div class="fw-bold text-info">
                                        ${escapeHtml(accumulatedHoursLabel)}
                                    </div>
                                    <div class="fs-10 text-600 mt-1">
                                        Total duration
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="fs-10 text-600">Logs</div>
                                    <div class="fw-bold text-primary">
                                        ${escapeHtml(proof.count || logs.length || 0)}
                                    </div>
                                    <div class="fs-10 text-600 mt-1">
                                        Records found
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="fs-10 text-600">Time In</div>
                                    <div class="fw-bold text-success">
                                        ${escapeHtml(timeInLabel)}
                                    </div>
                                    <div class="fs-10 text-600 mt-1">
                                        First biometrics log
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="fs-10 text-600">Time Out</div>
                                    <div class="fw-bold text-danger">
                                        ${escapeHtml(timeOutLabel)}
                                    </div>
                                    <div class="fs-10 text-600 mt-1">
                                        Last biometrics log
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border shadow-none">
                        <div class="card-header bg-body-tertiary">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <span class="fas fa-list me-2 text-primary"></span>
                                    Detailed Biometrics Logs
                                </h6>
                                <span class="badge bg-primary">
                                    ${escapeHtml(logs.length)} log(s)
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive scrollbar">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="bg-body-tertiary">
                                        <tr>
                                            <th>#</th>
                                            <th>Check Time</th>
                                            <th>Employee Name</th>
                                            <th>Biometric ID / Emp No.</th>
                                            <th>State</th>
                                            <th>Device</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${renderLogsRows(logs)}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `
            );
        }

        function syncSelectedEmployee() {
            const picker = el('employee_picker');
            const typeSelect = el('adjustment_type');
            const biometricIdInput = el('biometric_employee_id');
            const employeeNoInput = el('employee_no');
            const employeeNameInput = el('employee_name');

            if (!picker || !biometricIdInput || !employeeNoInput || !employeeNameInput) {
                return;
            }

            if (typeSelect && typeSelect.value === 'typhoon_disaster') {
                biometricIdInput.value = '';
                employeeNoInput.value = '';
                employeeNameInput.value = '';
                resetOffsetProofHiddenFields();
                return;
            }

            const selected = picker.options[picker.selectedIndex];

            if (!selected || !selected.value) {
                biometricIdInput.value = '';
                employeeNoInput.value = '';
                employeeNameInput.value = '';
                resetOffsetProofHiddenFields();
                return;
            }

            biometricIdInput.value = selected.dataset.biometricId || '';
            employeeNoInput.value = selected.dataset.employeeNo || '';
            employeeNameInput.value = selected.dataset.employeeName || '';

            resetOffsetProofHiddenFields();
        }

        function hideAllSections() {
            document.querySelectorAll('.adjustment-section').forEach(function(section) {
                section.classList.add('d-none');
            });

            [
                'work_date',
                'date_from',
                'date_to',
                'adjusted_time_in',
                'adjusted_time_out',
                'offset_source_date'
            ].forEach(function(id) {
                const field = el(id);

                if (field) {
                    field.required = false;
                }
            });
        }

        function showSection(name) {
            const section = document.querySelector('[data-section="' + name + '"]');

            if (section) {
                section.classList.remove('d-none');
            }
        }

        function setEmployeePickerMode(isRequired, message) {
            const picker = el('employee_picker');
            const help = el('employee_picker_help');

            if (picker) {
                picker.required = isRequired;
                picker.disabled = !isRequired;
            }

            if (help) {
                help.textContent = message;
            }
        }

        function refreshAdjustmentFields() {
            const typeSelect = el('adjustment_type');
            const workDateLabel = el('work_date_label');
            const workDateInput = el('work_date');
            const dateFromInput = el('date_from');
            const dateToInput = el('date_to');
            const timeInInput = el('adjusted_time_in');
            const timeOutInput = el('adjusted_time_out');
            const offsetSourceDateInput = el('offset_source_date');

            if (!typeSelect) {
                return;
            }

            const type = typeSelect.value;

            hideAllSections();

            setEmployeePickerMode(
                type !== 'typhoon_disaster',
                type === 'typhoon_disaster' ?
                'Employee selection is skipped. This applies to all employees with time-in on the selected date.' :
                'Required for individual adjustments. Automatically skipped for Typhoon / Disaster.'
            );

            if (type === 'sick_leave' || type === 'medical_leave') {
                showSection('leave');

                if (dateFromInput) {
                    dateFromInput.required = true;
                }

                if (dateToInput) {
                    dateToInput.required = true;
                }
            }

            if (type === 'change_schedule' || type === 'official_business' || type === 'holiday_work' || type ===
                'overtime') {
                showSection('single-date');
                showSection('manual-time');

                if (workDateLabel) {
                    workDateLabel.textContent = 'Work Date';
                }

                if (workDateInput) {
                    workDateInput.required = true;
                }

                if (timeInInput) {
                    timeInInput.required = true;
                }

                if (timeOutInput) {
                    timeOutInput.required = true;
                }
            }

            if (type === 'offset') {
                showSection('single-date');
                showSection('offset');

                if (workDateLabel) {
                    workDateLabel.textContent = 'Transfer / Offset Date';
                }

                if (workDateInput) {
                    workDateInput.required = true;
                }

                if (offsetSourceDateInput) {
                    offsetSourceDateInput.required = true;
                }
            }

            if (type === 'typhoon_disaster') {
                showSection('single-date');
                showSection('disaster');

                if (workDateLabel) {
                    workDateLabel.textContent = 'Typhoon / Disaster Date';
                }

                if (workDateInput) {
                    workDateInput.required = true;
                }
            }

            syncSelectedEmployee();
            resetOffsetProofHiddenFields();
        }

        async function checkOffsetProof(button) {
            if (!button) {
                button = el('check_offset_proof_btn');
            }

            if (!button) {
                openErrorModal('Check Biometrics Proof button was not found.');
                return;
            }

            const typeSelect = el('adjustment_type');
            const biometricIdInput = el('biometric_employee_id');
            const employeeNoInput = el('employee_no');
            const employeeNameInput = el('employee_name');
            const offsetSourceDateInput = el('offset_source_date');

            if (!typeSelect || typeSelect.value !== 'offset') {
                openErrorModal('Please select Offset as adjustment type first.');
                return;
            }

            if (!biometricIdInput || !employeeNameInput || !offsetSourceDateInput) {
                openErrorModal(
                    'Required fields are missing.',
                    'Please check biometric_employee_id, employee_name, and offset_source_date.'
                );
                return;
            }

            const biometricId = biometricIdInput.value.trim();
            const employeeNo = employeeNoInput ? employeeNoInput.value.trim() : '';
            const employeeName = employeeNameInput.value.trim();
            const offsetSourceDate = offsetSourceDateInput.value.trim();

            if (!biometricId || !employeeName || !offsetSourceDate) {
                openErrorModal(
                    'Please select employee and biometric proof date first.',
                    `
                        Bio ID: ${escapeHtml(biometricId || 'empty')} |
                        Employee: ${escapeHtml(employeeName || 'empty')} |
                        Proof Date: ${escapeHtml(offsetSourceDate || 'empty')}
                    `
                );
                return;
            }

            const url = button.dataset.url;

            if (!url) {
                openErrorModal('Offset proof URL is missing.', 'Check the data-url attribute of the button.');
                return;
            }

            const params = new URLSearchParams({
                biometric_employee_id: biometricId,
                employee_no: employeeNo,
                employee_name: employeeName,
                offset_source_date: offsetSourceDate
            });

            const controller = new AbortController();

            const timeout = setTimeout(function() {
                controller.abort();
            }, 15000);

            resetOffsetProofHiddenFields();

            button.disabled = true;
            button.innerHTML = `
                <span class="fas fa-spinner fa-spin me-1"></span>
                Checking...
            `;

            openLoadingModal(employeeName, offsetSourceDate);

            try {
                const response = await fetch(`${url}?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    signal: controller.signal
                });

                clearTimeout(timeout);

                const contentType = response.headers.get('content-type') || '';

                if (!contentType.includes('application/json')) {
                    const text = await response.text();

                    openErrorModal(
                        'Server did not return JSON.',
                        `
                            Status: ${escapeHtml(response.status)}
                            <br>
                            Check Laravel log: <code>storage/logs/laravel.log</code>
                        `
                    );

                    console.error('Offset proof non-JSON response:', text);
                    return;
                }

                const data = await response.json();

                if (!response.ok || !data.found) {
                    openNoProofModal(data, employeeName, offsetSourceDate);
                    return;
                }

                openProofModal(data.proof || {}, employeeName, offsetSourceDate);
            } catch (error) {
                clearTimeout(timeout);

                if (error.name === 'AbortError') {
                    openErrorModal(
                        'Request timeout.',
                        'The biometrics proof check took more than 15 seconds. The backend query may be slow or the route is not responding.'
                    );
                } else {
                    openErrorModal('Unable to check biometric proof.', escapeHtml(error.message));
                }

                console.error('Offset proof error:', error);
            } finally {
                button.disabled = false;
                button.innerHTML = `
                    <span class="fas fa-search me-1"></span>
                    Check Biometrics Proof
                `;
            }
        }

        function initPayrollAdjustmentForm() {
            const picker = el('employee_picker');
            const typeSelect = el('adjustment_type');
            const checkButton = el('check_offset_proof_btn');
            const offsetSourceDateInput = el('offset_source_date');

            if (picker) {
                picker.addEventListener('change', syncSelectedEmployee);
            }

            if (typeSelect) {
                typeSelect.addEventListener('change', refreshAdjustmentFields);
            }

            if (offsetSourceDateInput) {
                offsetSourceDateInput.addEventListener('change', resetOffsetProofHiddenFields);
            }

            if (checkButton) {
                checkButton.addEventListener('click', function(event) {
                    event.preventDefault();
                    checkOffsetProof(event.currentTarget);
                });
            }

            syncSelectedEmployee();
            refreshAdjustmentFields();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPayrollAdjustmentForm);
        } else {
            initPayrollAdjustmentForm();
        }
    })();
</script>
