@extends('layouts.app')
@section('title', 'Biometrics Sync - HR')

@push('styles')
    <style>
        .biometric-source-card {
            display: block;
            padding: .75rem .85rem;
            border: 1px solid var(--falcon-border-color, #d8e2ef);
            border-radius: .5rem;
            background: var(--falcon-card-bg, #fff);
            cursor: pointer;
            transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }

        .biometric-source-card:hover {
            border-color: var(--falcon-primary, #2c7be5);
            box-shadow: 0 .125rem .5rem rgba(0, 0, 0, .06);
            transform: translateY(-1px);
        }

        .biometric-source-card:has(.form-check-input:checked) {
            border-color: var(--falcon-primary, #2c7be5);
            background: rgba(44, 123, 229, .04);
        }

        .sync-stat-box {
            height: 100%;
            padding: .7rem .8rem;
            border: 1px solid var(--falcon-border-color, #d8e2ef);
            border-radius: .5rem;
            background: var(--falcon-body-bg, #fff);
        }
    </style>
@endpush

@section('content')
    <div class="container" data-layout="container">
        <script>
            (function() {
                const isFluid = JSON.parse(localStorage.getItem('isFluid') || 'false');

                if (!isFluid) {
                    return;
                }

                const container = document.querySelector('[data-layout]');

                if (!container) {
                    return;
                }

                container.classList.remove('container');
                container.classList.add('container-fluid');
            })();
        </script>

        <div class="content">
            <div class="card monitor-card shadow-sm mb-3 border-0">
                <div class="card-header bg-body-tertiary border-bottom border-200 py-3">
                    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fas fa-fingerprint text-primary"></span>
                                <h5 class="mb-0">Biometrics Sync</h5>
                            </div>
                            <small class="text-muted">
                                Sync one, several, or all configured CrossChex biometric sources directly from this page.
                            </small>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <span class="fas fa-bolt me-1"></span>No queue worker required
                            </span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                <span class="fas fa-copy me-1"></span>Existing records ignored
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @cannot('mirasol-logs.sync')
                        <div class="alert alert-secondary mb-0">
                            You can review attendance monitoring records, but your role does not have permission to run a
                            biometric synchronization.
                        </div>
                    @elseif (empty($syncAccounts))
                        <div class="alert alert-warning mb-0">
                            <div class="fw-semibold">No CrossChex sources are fully configured.</div>
                            <div class="small mt-1">
                                Add each source URL, API key, and API secret in your <code>.env</code>, then run
                                <code>php artisan optimize:clear</code>.
                            </div>
                        </div>
                    @else
                        <form id="syncForm">
                            @csrf

                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-12 col-md-4 col-xl-3">
                                    <label class="form-label mb-1 fw-semibold">Start Date</label>
                                    <input type="date" name="from" class="form-control form-control-sm" required
                                        value="{{ old('from', now()->toDateString()) }}">
                                </div>

                                <div class="col-12 col-md-4 col-xl-3">
                                    <label class="form-label mb-1 fw-semibold">End Date</label>
                                    <input type="date" name="to" class="form-control form-control-sm" required
                                        value="{{ old('to', now()->toDateString()) }}">
                                </div>

                                <div class="col-12 col-md-4 col-xl-6">
                                    <div class="d-flex flex-wrap justify-content-md-end gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllSourcesBtn">
                                            <span class="fas fa-check-double me-1"></span>Select All
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearSourcesBtn">
                                            Clear
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold mb-2">Biometric Sources</label>
                                <div class="row g-2" id="biometricSourceGrid">
                                    @foreach ($syncAccounts as $accountKey => $account)
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <label class="biometric-source-card w-100 h-100">
                                                <div class="d-flex align-items-start gap-2">
                                                    <input class="form-check-input mt-1 biometric-source-checkbox" type="checkbox"
                                                        name="accounts[]" value="{{ $accountKey }}" checked>
                                                    <div class="min-w-0">
                                                        <div class="fw-semibold text-body">{{ $account['name'] }}</div>
                                                        <div class="small text-muted text-truncate">
                                                            CrossChex source: {{ $accountKey }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                                <div class="small text-muted">
                                    Fast mode inserts new attendance transactions only. Duplicate CrossChex IDs already in the
                                    database are ignored by the database unique index without updating the existing row.
                                </div>

                                <div class="d-flex gap-2 flex-shrink-0">
                                    <button id="syncSelectedBtn" class="btn btn-primary btn-sm" type="submit">
                                        <span class="fas fa-sync me-1"></span>Sync Selected
                                    </button>
                                    <button id="syncAllBtn" class="btn btn-success btn-sm" type="button">
                                        <span class="fas fa-cloud-download-alt me-1"></span>Sync All
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card jo-card shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom border-200">
                    <div class="d-flex flex-column flex-lg-row gap-2 align-items-lg-center justify-content-between">
                        <div>
                            <h5 class="mb-0">Attendance Monitoring Summary</h5>
                            <small class="text-muted">
                                Cutoff Coverage: <strong>{{ $cutoffLabel }}</strong>
                            </small>
                        </div>

                        <form method="GET" action="{{ route('mirasol-logs.index') }}" class="row g-2 align-items-center">
                            <div class="col-auto">
                                <input type="text" name="q" list="employeeSuggestions"
                                    class="form-control form-control-sm" style="width: 260px;"
                                    placeholder="Search employee name / employee no..." value="{{ request('q') }}"
                                    required>

                                <datalist id="employeeSuggestions">
                                    @foreach ($people as $p)
                                        @if (!empty($p['employee_name']))
                                            <option value="{{ $p['employee_name'] }}">
                                                {{ $p['employee_name'] }}{{ !empty($p['employee_no']) ? ' - ' . $p['employee_no'] : '' }}
                                            </option>
                                        @endif

                                        @if (!empty($p['employee_no']))
                                            <option value="{{ $p['employee_no'] }}">
                                                {{ $p['employee_name'] }}
                                            </option>
                                        @endif
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="col-auto">
                                <select name="cutoff_month" class="form-select form-select-sm">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}"
                                            {{ (int) $cutoffMonth === $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-auto">
                                <select name="cutoff_year" class="form-select form-select-sm">
                                    @for ($y = now()->year - 2; $y <= now()->year + 3; $y++)
                                        <option value="{{ $y }}"
                                            {{ (int) $cutoffYear === $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-auto">
                                <select name="cutoff_type" class="form-select form-select-sm">
                                    <option value="26_10" {{ $cutoffType === '26_10' ? 'selected' : '' }}>
                                        {{ config('payroll.cutoff_display_by_range.26_10', '1st Cutoff (26-10)') }}
                                    </option>

                                    <option value="11_25" {{ $cutoffType === '11_25' ? 'selected' : '' }}>
                                        {{ config('payroll.cutoff_display_by_range.11_25', '2nd Cutoff (11-25)') }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-auto">
                                <button class="btn btn-outline-secondary btn-sm" type="submit">
                                    Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive mb-0">
                    <table class="table table-sm table-hover mb-0 fs-10 align-middle jo-table">
                        <thead class="bg-body-tertiary border-bottom border-200">
                            <tr>
                                <th class="ps-3" style="width:60px;">#</th>
                                <th style="width:220px;">Employee Name</th>
                                <th style="width:120px;">Employee No</th>
                                <th style="width:170px;">Date</th>
                                <th style="width:150px;">Shift</th>
                                <th style="width:190px;">Plotted Schedule</th>
                                <th style="width:110px;">Day Off</th>
                                <th style="width:110px;">Time In</th>
                                <th style="width:110px;">Time Out</th>
                                <th style="width:100px;">Clock Span</th>
                                <th style="width:100px;">Required Clock</th>
                                <th style="width:100px;">Late</th>
                                <th style="width:100px;">Undertime</th>
                                <th style="width:180px;">Attendance Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($rows as $i => $r)
                                <tr>
                                    <td class="ps-3 text-muted">
                                        {{ ($rows->firstItem() ?? 0) + $i }}
                                    </td>

                                    <td class="fw-semi-bold">
                                        {{ $r['employee_name'] ?? '—' }}

                                        @if (!empty($r['remarks']))
                                            <div class="text-muted fs-11">
                                                {{ $r['remarks'] }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="text-muted">
                                        {{ $r['employee_no'] ?: '—' }}
                                    </td>

                                    <td class="text-muted">
                                        {{ $r['log_date'] ? \Carbon\Carbon::parse($r['log_date'])->format('F d, Y (l)') : '—' }}
                                    </td>

                                    <td>
                                        @if (!empty($r['shift_name']))
                                            @if (($r['shift_mode'] ?? '') === 'Flexible')
                                                <span class="badge bg-info-subtle text-info border">
                                                    Flexible Shift
                                                </span>
                                            @else
                                                <span class="badge bg-primary-subtle text-primary border">
                                                    Regular Shift
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                No Shift
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if (($r['schedule_status'] ?? null) === 'scheduled')
                                            @if (($r['shift_mode'] ?? '') === 'Flexible')
                                                <div class="fw-semibold text-info">
                                                    Flexible - {{ $r['required_hours_label'] ?? '—' }} Clock Hours Required
                                                </div>
                                                <div class="fs-11 text-muted">
                                                    {{ number_format(((int) ($r['paid_work_minutes'] ?? 480)) / 60, 0) }} paid hour(s) + lunch; no fixed Time In / Time Out
                                                </div>
                                            @else
                                                <div class="fw-semibold text-success">
                                                    {{ !empty($r['scheduled_time_in']) ? \Carbon\Carbon::parse($r['scheduled_time_in'])->format('h:i A') : 'No Time In' }}
                                                    -
                                                    {{ !empty($r['scheduled_time_out']) ? \Carbon\Carbon::parse($r['scheduled_time_out'])->format('h:i A') : 'No Time Out' }}
                                                </div>
                                                <div class="fs-11 text-muted">
                                                    Grace: {{ $r['grace_minutes'] ?? 15 }} min
                                                </div>
                                            @endif
                                        @elseif (($r['schedule_status'] ?? null) === 'rest_day')
                                            <span class="badge bg-warning-subtle text-warning border">
                                                Rest Day
                                            </span>
                                        @elseif (($r['schedule_status'] ?? null) === 'leave')
                                            <span class="badge bg-info-subtle text-info border">
                                                Leave
                                            </span>
                                        @elseif (($r['schedule_status'] ?? null) === 'holiday')
                                            <span class="badge bg-danger-subtle text-danger border">
                                                Holiday
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                No Schedule
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if (!empty($r['day_off']))
                                            <span class="badge bg-warning-subtle text-warning border">
                                                {{ $r['day_off'] }}
                                            </span>
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ !empty($r['actual_time_in']) ? \Carbon\Carbon::parse($r['actual_time_in'])->format('h:i A') : '—' }}
                                    </td>

                                    <td>
                                        {{ !empty($r['actual_time_out']) ? \Carbon\Carbon::parse($r['actual_time_out'])->format('h:i A') : '—' }}
                                    </td>

                                    <td class="fw-semibold">
                                        {{ $r['worked_hours_label'] ?? '—' }}
                                    </td>

                                    <td>
                                        {{ $r['required_hours_label'] ?? '—' }}
                                    </td>

                                    <td>
                                        @if (($r['late_minutes'] ?? 0) > 0)
                                            <span class="badge bg-warning-subtle text-warning border">
                                                {{ $r['late_label'] }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td>
                                        @if (($r['undertime_minutes'] ?? 0) > 0)
                                            <span class="badge bg-danger-subtle text-danger border">
                                                {{ $r['undertime_label'] }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td>
                                        <div
                                            class="small border rounded px-2 py-1 bg-{{ $r['attendance_class'] ?? 'secondary' }}-subtle text-{{ $r['attendance_class'] ?? 'secondary' }}">
                                            {{ $r['attendance_note'] ?? '—' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center">
                                        <div class="empty-state py-4">
                                            <div class="icon">
                                                <span class="fas fa-fingerprint"></span>
                                            </div>

                                            @if (!($isSearch ?? false))
                                                <div class="fw-bold">Search Employee First</div>
                                                <div class="text-muted fs-11">
                                                    Please search an employee name or employee number to view cutoff
                                                    attendance logs.
                                                </div>
                                            @else
                                                <div class="fw-bold">No Records Found</div>
                                                <div class="text-muted fs-11">
                                                    No biometrics logs or plotting schedule found for the selected employee
                                                    and cutoff.
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($isSearch ?? false)
                    <div class="card-footer bg-body-tertiary border-top border-200">
                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
                            <small class="text-muted">
                                Showing {{ $rows->firstItem() ?? 0 }} to {{ $rows->lastItem() ?? 0 }} of
                                {{ $rows->total() }}
                            </small>

                            <div class="ms-md-auto">
                                {{ $rows->links('pagination.custom') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="syncModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 14px;">
                <div class="modal-header bg-body-tertiary border-bottom border-200">
                    <div>
                        <h5 class="modal-title mb-1">
                            <span class="fas fa-fingerprint text-primary me-2"></span>Biometrics Sync
                        </h5>
                        <div class="small text-muted">Browser-driven synchronization — no queue worker or cron job.</div>
                    </div>
                </div>

                <div class="modal-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                        <div class="small fw-semibold" id="syncStatusText">Preparing...</div>
                        <div class="small text-muted" id="syncCurrentSource">Source: -</div>
                    </div>

                    <div class="progress mb-3" style="height: 14px;">
                        <div id="syncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: 0%">0%</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="sync-stat-box">
                                <div class="text-muted small">Page</div>
                                <div class="fw-bold" id="syncPageStat">-</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="sync-stat-box">
                                <div class="text-muted small">New Records</div>
                                <div class="fw-bold text-success" id="syncSavedStat">0</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="sync-stat-box">
                                <div class="text-muted small">Already Saved</div>
                                <div class="fw-bold text-info" id="syncSkippedStat">0</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="sync-stat-box">
                                <div class="text-muted small">Invalid Skipped</div>
                                <div class="fw-bold text-warning" id="syncInvalidStat">0</div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-3 overflow-hidden mb-3">
                        <div class="bg-body-tertiary border-bottom px-3 py-2 small fw-semibold">Source Progress</div>
                        <div id="syncAccountStats" class="list-group list-group-flush"></div>
                    </div>

                    <div class="alert alert-info py-2 mb-0 small" id="syncInfoBox">
                        Keep this page open while synchronization is running. If the page is refreshed, the browser will
                        attempt to resume the current sync session. Re-running the same range is safe because existing records
                        are ignored.
                    </div>
                    <div class="alert alert-warning mt-3 d-none" id="syncWarnBox"></div>
                    <div class="alert alert-danger mt-3 d-none" id="syncErrorBox"></div>
                </div>

                <div class="modal-footer bg-body-tertiary border-top border-200">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="syncCloseBtn"
                        data-bs-dismiss="modal" disabled>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('syncForm');

            if (!form) {
                return;
            }

            const modalEl = document.getElementById('syncModal');
            const modal = new bootstrap.Modal(modalEl);
            const statusEl = document.getElementById('syncStatusText');
            const progressEl = document.getElementById('syncProgressBar');
            const currentSourceEl = document.getElementById('syncCurrentSource');
            const pageEl = document.getElementById('syncPageStat');
            const savedEl = document.getElementById('syncSavedStat');
            const skippedEl = document.getElementById('syncSkippedStat');
            const invalidEl = document.getElementById('syncInvalidStat');
            const accountStatsEl = document.getElementById('syncAccountStats');
            const warningEl = document.getElementById('syncWarnBox');
            const errorEl = document.getElementById('syncErrorBox');
            const closeBtn = document.getElementById('syncCloseBtn');
            const syncSelectedBtn = document.getElementById('syncSelectedBtn');
            const syncAllBtn = document.getElementById('syncAllBtn');
            const selectAllBtn = document.getElementById('selectAllSourcesBtn');
            const clearBtn = document.getElementById('clearSourcesBtn');
            const resumeStorageKey = 'crosschex_browser_sync_job_id';
            const csrfToken = form.querySelector('input[name="_token"]')?.value || '';

            let running = false;

            const checkboxes = () => Array.from(document.querySelectorAll('.biometric-source-checkbox'));
            const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));

            const setMessage = (element, message) => {
                if (!element) {
                    return;
                }

                element.textContent = message || '';
                element.classList.toggle('d-none', !message);
            };

            const parseJson = async response => {
                const raw = await response.text();

                try {
                    return { json: JSON.parse(raw), raw };
                } catch (error) {
                    return { json: null, raw };
                }
            };

            const updateProgress = percent => {
                const value = Math.max(0, Math.min(100, Number(percent) || 0));
                progressEl.style.width = `${value}%`;
                progressEl.textContent = `${value}%`;
                progressEl.setAttribute('aria-valuenow', String(value));
            };

            const escapeHtml = value => String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const renderAccountStats = stats => {
                const entries = Object.entries(stats || {});

                if (!entries.length) {
                    accountStatsEl.innerHTML = '<div class="px-3 py-2 small text-muted">Preparing sources...</div>';
                    return;
                }

                accountStatsEl.innerHTML = entries.map(([key, item]) => {
                    const stateClass = item.done ? 'text-success' : 'text-muted';
                    const stateIcon = item.done ? 'fa-check-circle text-success' : 'fa-circle-notch text-muted';
                    const pages = item.page_count
                        ? `${Number(item.pages_done || 0)} / ${Number(item.page_count)}`
                        : `${Number(item.pages_done || 0)} / -`;

                    return `
                        <div class="list-group-item px-3 py-2">
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-1">
                                <div class="small fw-semibold">
                                    <span class="fas ${stateIcon} me-2"></span>${escapeHtml(item.name || key)}
                                </div>
                                <div class="small ${stateClass}">
                                    Pages ${pages} &nbsp;|&nbsp; New ${Number(item.inserted || 0).toLocaleString()}
                                    &nbsp;|&nbsp; Ignored ${Number(item.skipped || 0).toLocaleString()}
                                </div>
                            </div>
                        </div>`;
                }).join('');
            };

            const applyState = data => {
                statusEl.textContent = data.message || data.state || 'Synchronizing...';
                currentSourceEl.textContent = `Source: ${data.accountName || '-'}`;
                pageEl.textContent = data.page
                    ? `${data.page}${data.pageCount ? ' / ' + data.pageCount : ''}`
                    : '-';
                savedEl.textContent = Number(data.saved || 0).toLocaleString();
                skippedEl.textContent = Number(data.skipped || 0).toLocaleString();
                invalidEl.textContent = Number(data.invalid || 0).toLocaleString();
                updateProgress(data.percent || 0);
                renderAccountStats(data.accountStats || {});
                setMessage(warningEl, data.retryAfter > 0
                    ? `CrossChex requested a short pause. Retrying automatically in ${data.retryAfter} second(s)...`
                    : '');
                setMessage(errorEl, data.error || '');
            };

            const finishUi = (success = true) => {
                running = false;
                closeBtn.disabled = false;
                syncSelectedBtn.disabled = false;
                syncAllBtn.disabled = false;

                if (success) {
                    progressEl.classList.remove('progress-bar-animated');
                }
            };

            const postJobStep = async jobId => {
                const body = new FormData();
                body.append('_token', csrfToken);
                body.append('job', jobId);

                const response = await fetch(`{{ route('mirasol-logs.sync-step') }}`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body
                });

                const parsed = await parseJson(response);

                if (!parsed.json) {
                    throw new Error('The server returned a non-JSON response. Check storage/logs/laravel.log.');
                }

                if (response.status === 419) {
                    throw new Error('Session expired. Refresh the page and run the sync again.');
                }

                return parsed.json;
            };

            const runSteps = async jobId => {
                if (running) {
                    return;
                }

                running = true;
                closeBtn.disabled = true;
                syncSelectedBtn.disabled = true;
                syncAllBtn.disabled = true;
                progressEl.classList.add('progress-bar-animated');

                try {
                    while (running) {
                        const data = await postJobStep(jobId);
                        applyState(data);

                        if (data.state === 'error' || data.error) {
                            localStorage.removeItem(resumeStorageKey);
                            finishUi(false);
                            return;
                        }

                        if (data.done) {
                            localStorage.removeItem(resumeStorageKey);
                            finishUi(true);

                            setTimeout(() => {
                                window.location.reload();
                            }, 900);

                            return;
                        }

                        const waitMs = data.retryAfter > 0
                            ? Number(data.retryAfter) * 1000
                            : 150;

                        await sleep(waitMs);
                    }
                } catch (error) {
                    console.error(error);
                    setMessage(errorEl, error.message || 'Biometric synchronization failed.');
                    statusEl.textContent = 'Sync interrupted.';
                    finishUi(false);
                }
            };

            const startSync = async () => {
                const selected = checkboxes().filter(checkbox => checkbox.checked);
                const from = form.querySelector('input[name="from"]')?.value;
                const to = form.querySelector('input[name="to"]')?.value;

                if (!from || !to) {
                    alert('Please select Start Date and End Date.');
                    return;
                }

                if (to < from) {
                    alert('End Date must be after or equal to Start Date.');
                    return;
                }

                if (!selected.length) {
                    alert('Select at least one biometric source.');
                    return;
                }

                setMessage(errorEl, '');
                setMessage(warningEl, '');
                updateProgress(0);
                savedEl.textContent = '0';
                skippedEl.textContent = '0';
                invalidEl.textContent = '0';
                pageEl.textContent = '-';
                currentSourceEl.textContent = 'Source: -';
                accountStatsEl.innerHTML = '<div class="px-3 py-2 small text-muted">Preparing sources...</div>';
                statusEl.textContent = 'Starting synchronization...';
                closeBtn.disabled = true;
                modal.show();

                try {
                    const response = await fetch(`{{ route('mirasol-logs.sync-start') }}`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: new FormData(form)
                    });

                    const parsed = await parseJson(response);
                    const data = parsed.json;

                    if (!data) {
                        throw new Error('The server returned a non-JSON response. Check storage/logs/laravel.log.');
                    }

                    if (!response.ok || !data.ok) {
                        const validation = Object.values(data.errors || {}).flat().join('\n');
                        throw new Error(validation || data.message || `Unable to start sync (${response.status}).`);
                    }

                    applyState(data);
                    localStorage.setItem(resumeStorageKey, data.jobId);
                    await runSteps(data.jobId);
                } catch (error) {
                    console.error(error);
                    setMessage(errorEl, error.message || 'Unable to start biometric synchronization.');
                    statusEl.textContent = 'Unable to start sync.';
                    finishUi(false);
                }
            };

            const resumeIfNeeded = async () => {
                const jobId = localStorage.getItem(resumeStorageKey);

                if (!jobId) {
                    return;
                }

                try {
                    const response = await fetch(
                        `{{ route('mirasol-logs.sync-status') }}?job=${encodeURIComponent(jobId)}`,
                        { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
                    );

                    const parsed = await parseJson(response);
                    const data = parsed.json;

                    if (!response.ok || !data) {
                        localStorage.removeItem(resumeStorageKey);
                        return;
                    }

                    applyState(data);

                    if (data.done || data.state === 'error') {
                        localStorage.removeItem(resumeStorageKey);
                        return;
                    }

                    modal.show();
                    await runSteps(jobId);
                } catch (error) {
                    console.error('Unable to resume biometric sync:', error);
                }
            };

            form.addEventListener('submit', event => {
                event.preventDefault();
                startSync();
            });

            syncAllBtn.addEventListener('click', () => {
                checkboxes().forEach(checkbox => {
                    checkbox.checked = true;
                });
                startSync();
            });

            selectAllBtn.addEventListener('click', () => {
                checkboxes().forEach(checkbox => {
                    checkbox.checked = true;
                });
            });

            clearBtn.addEventListener('click', () => {
                checkboxes().forEach(checkbox => {
                    checkbox.checked = false;
                });
            });

            modalEl.addEventListener('hidden.bs.modal', () => {
                setMessage(errorEl, '');
                setMessage(warningEl, '');
            });

            resumeIfNeeded();
        });
    </script>
@endpush
