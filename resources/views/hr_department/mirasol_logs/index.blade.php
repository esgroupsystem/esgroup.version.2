@extends('layouts.app')
@section('title', 'Mirasol Biometrics Logs - HR')

@section('content')
    <div class="container" data-layout="container">
        <script>
            (function() {
                const isFluid = JSON.parse(localStorage.getItem('isFluid') || 'false');
                if (!isFluid) return;
                const container = document.querySelector('[data-layout]');
                if (!container) return;
                container.classList.remove('container');
                container.classList.add('container-fluid');
            })();
        </script>

        <div class="content">
            {{-- SYNC CARD --}}
            <div class="card monitor-card shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom border-200">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                        <div>
                            <h6 class="mb-0">Sync Logs</h6>
                            <small class="text-muted">Select start and end date to sync logs from device/source</small>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form id="syncForm" class="row g-2 align-items-end">
                        @csrf

                        <div class="col-12 col-lg-3">
                            <label class="form-label mb-1">Start Date</label>
                            <input type="date" name="from" class="form-control form-control-sm date-field" required
                                value="{{ old('from', now()->toDateString()) }}">
                        </div>

                        <div class="col-12 col-lg-3">
                            <label class="form-label mb-1">End Date</label>
                            <input type="date" name="to" class="form-control form-control-sm date-field" required
                                value="{{ old('to', now()->toDateString()) }}">
                        </div>

                        <div class="col-12 col-lg-6 d-flex gap-2">
                            <button id="syncBtn" class="btn btn-primary btn-sm flex-grow-1" type="submit">
                                <span class="fas fa-sync me-1"></span> Sync Now
                            </button>
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('mirasol-logs.index') }}">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TABLE CARD --}}
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
                                    <option value="11_25" {{ $cutoffType === '11_25' ? 'selected' : '' }}>
                                        11 - 25
                                    </option>
                                    <option value="26_10" {{ $cutoffType === '26_10' ? 'selected' : '' }}>
                                        26 - 10
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
                                <th style="width:170px;">Plotted Schedule</th>
                                <th style="width:110px;">Time In</th>
                                <th style="width:110px;">Time Out</th>
                                <th style="width:100px;">Total Hours</th>
                                <th style="width:100px;">Late</th>
                                <th style="width:100px;">Undertime</th>
                                <th style="width:120px;">Attendance Status</th>
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
                                        @if (!empty($r['shift_name']))
                                            <div class="text-muted fs-11">{{ $r['shift_name'] }}</div>
                                        @endif
                                    </td>

                                    <td class="text-muted">
                                        {{ $r['employee_no'] ?: '—' }}
                                    </td>

                                    <td class="text-muted">
                                        {{ $r['log_date'] ? \Carbon\Carbon::parse($r['log_date'])->format('F d, Y (l)') : '—' }}
                                    </td>

                                    <td>
                                        @if (($r['schedule_status'] ?? null) === 'scheduled')
                                            <div class="fw-semibold text-success">
                                                {{ !empty($r['scheduled_time_in']) ? \Carbon\Carbon::parse($r['scheduled_time_in'])->format('h:i A') : '—' }}
                                                -
                                                {{ !empty($r['scheduled_time_out']) ? \Carbon\Carbon::parse($r['scheduled_time_out'])->format('h:i A') : '—' }}
                                            </div>
                                            <div class="fs-11 text-muted">
                                                Grace: {{ $r['grace_minutes'] ?? 15 }} min
                                            </div>
                                        @elseif (($r['schedule_status'] ?? null) === 'rest_day')
                                            <span class="badge bg-warning-subtle text-warning border">Rest Day</span>
                                        @elseif (($r['schedule_status'] ?? null) === 'leave')
                                            <span class="badge bg-info-subtle text-info border">Leave</span>
                                        @elseif (($r['schedule_status'] ?? null) === 'holiday')
                                            <span class="badge bg-danger-subtle text-danger border">Holiday</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border">No Schedule</span>
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
                                    <td colspan="11" class="text-center">
                                        <div class="empty-state py-4">
                                            <div class="icon"><span class="fas fa-fingerprint"></span></div>

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
                            <div class="ms-md-auto">{{ $rows->links('pagination.custom') }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- SYNC PROGRESS MODAL --}}
    <div class="modal fade" id="syncModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content" style="border-radius:14px;">
                <div class="modal-header">
                    <h5 class="modal-title mb-0">Syncing Logs...</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="small text-muted mb-2" id="syncStatusText">Preparing...</div>

                    <div class="progress" style="height: 12px;">
                        <div id="syncProgressBar" class="progress-bar" role="progressbar" style="width:0%"></div>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <div class="small text-muted" id="syncMetaLeft">Page: -</div>
                        <div class="small text-muted" id="syncMetaRight">Saved: 0</div>
                    </div>

                    <div class="alert alert-danger mt-3 d-none" id="syncErrorBox"></div>
                    <div class="alert alert-warning mt-3 d-none" id="syncWarnBox"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const need = [
                'syncForm',
                'syncModal',
                'syncStatusText',
                'syncProgressBar',
                'syncMetaLeft',
                'syncMetaRight'
            ];

            const missing = need.filter(id => !document.getElementById(id));
            if (missing.length) {
                console.error('Sync UI missing elements:', missing.map(x => '#' + x).join(', '));
                return;
            }

            const form = document.getElementById('syncForm');
            const modalEl = document.getElementById('syncModal');
            const statusEl = document.getElementById('syncStatusText');
            const barEl = document.getElementById('syncProgressBar');
            const metaL = document.getElementById('syncMetaLeft');
            const metaR = document.getElementById('syncMetaRight');
            const syncBtn = document.getElementById('syncBtn');

            const modalBody = modalEl.querySelector('.modal-body');

            let errBox = document.getElementById('syncErrorBox');
            if (!errBox && modalBody) {
                errBox = document.createElement('div');
                errBox.id = 'syncErrorBox';
                errBox.className = 'alert alert-danger mt-3 d-none';
                modalBody.appendChild(errBox);
            }

            let warnBox = document.getElementById('syncWarnBox');
            if (!warnBox && modalBody) {
                warnBox = document.createElement('div');
                warnBox.id = 'syncWarnBox';
                warnBox.className = 'alert alert-warning mt-3 d-none';
                modalBody.appendChild(warnBox);
            }

            if (!window.bootstrap || !bootstrap.Modal) {
                console.error('Bootstrap Modal missing. Load bootstrap.bundle.js');
                return;
            }

            const modal = new bootstrap.Modal(modalEl);

            const show = (el, msg) => {
                if (!el) return;
                el.textContent = msg || '';
                el.classList.toggle('d-none', !msg);
            };

            const setBar = (pct) => {
                const p = Math.max(0, Math.min(100, Number(pct) || 0));
                barEl.style.width = p + '%';
                barEl.textContent = p ? (p + '%') : '';
            };

            let pollTimer = null;
            let queuedTimer = null;

            const stop = () => {
                if (pollTimer) clearInterval(pollTimer);
                pollTimer = null;
                if (queuedTimer) clearTimeout(queuedTimer);
                queuedTimer = null;
            };

            const parseJson = async (res) => {
                const text = await res.text();
                try {
                    return {
                        ok: true,
                        json: JSON.parse(text),
                        raw: text
                    };
                } catch {
                    return {
                        ok: false,
                        json: null,
                        raw: text
                    };
                }
            };

            const poll = async (jobId) => {
                try {
                    const res = await fetch(
                        `{{ route('mirasol-logs.sync-status') }}?job=${encodeURIComponent(jobId)}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                    const parsed = await parseJson(res);
                    if (!parsed.ok) {
                        console.error('Non-JSON status:', parsed.raw);
                        show(errBox, 'Server returned non-JSON (check laravel.log).');
                        return;
                    }

                    const data = parsed.json || {};
                    if (!res.ok) {
                        show(errBox, data.message || `Request failed (${res.status}).`);
                        if (res.status === 404) stop();
                        return;
                    }
                    if (!data.ok) return;

                    statusEl.textContent = data.message || data.state || '...';
                    metaL.textContent =
                        `Page: ${data.page ?? '-'}${data.pageCount ? ' / ' + data.pageCount : ''}`;
                    metaR.textContent = `Saved: ${data.saved ?? 0}`;

                    show(errBox, data.error || '');
                    if (data.percent !== null && data.percent !== undefined) setBar(data.percent);

                    if ((data.state === 'queued' || data.page === 0) && !queuedTimer) {
                        queuedTimer = setTimeout(() => {
                            show(warnBox, 'Still queued. If stuck, run: php artisan queue:work');
                        }, 12000);
                    }
                    if (data.state === 'running') show(warnBox, '');

                    if (data.done) {
                        stop();
                        setTimeout(() => {
                            const url = new URL(window.location.href);
                            window.location.href = url.toString();
                        }, 700);
                    }
                } catch (e) {
                    console.error(e);
                    show(errBox, 'Polling failed. Check console/network.');
                }
            };

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                stop();
                setBar(0);
                show(errBox, '');
                show(warnBox, '');

                const from = form.querySelector('input[name="from"]')?.value;
                const to = form.querySelector('input[name="to"]')?.value;

                if (!from || !to) return show(errBox, 'Please select Start Date and End Date.');
                if (to < from) return show(errBox, 'End Date must be after or equal to Start Date.');

                statusEl.textContent = 'Starting...';
                metaL.textContent = 'Page: -';
                metaR.textContent = 'Saved: 0';

                modal.show();
                if (syncBtn) syncBtn.disabled = true;

                try {
                    const res = await fetch(`{{ route('mirasol-logs.sync-start') }}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    });

                    if (res.status === 419) {
                        show(errBox, 'Session expired (CSRF). Refresh the page then try again.');
                        statusEl.textContent = 'Failed.';
                        if (syncBtn) syncBtn.disabled = false;
                        return;
                    }

                    const parsed = await parseJson(res);
                    if (!parsed.ok) {
                        console.error('Non-JSON start:', parsed.raw);
                        show(errBox, 'Server returned non-JSON (check laravel.log).');
                        statusEl.textContent = 'Failed.';
                        if (syncBtn) syncBtn.disabled = false;
                        return;
                    }

                    const data = parsed.json || {};

                    if (res.status === 422) {
                        const errs = data.errors || {};
                        const msg = Object.values(errs).flat().join('\n') || data.message ||
                            'Validation failed.';
                        show(errBox, msg);
                        statusEl.textContent = 'Failed.';
                        if (syncBtn) syncBtn.disabled = false;
                        return;
                    }

                    if (!res.ok || !data.ok) {
                        show(errBox, data.message || `Failed to start sync (${res.status}).`);
                        statusEl.textContent = 'Failed.';
                        if (syncBtn) syncBtn.disabled = false;
                        return;
                    }

                    pollTimer = setInterval(() => poll(data.jobId), 1000);
                    poll(data.jobId);
                } catch (e) {
                    console.error(e);
                    show(errBox, 'Failed to start sync. Check console/network.');
                    statusEl.textContent = 'Failed.';
                    if (syncBtn) syncBtn.disabled = false;
                }
            });

            modalEl.addEventListener('hidden.bs.modal', () => {
                stop();
                if (syncBtn) syncBtn.disabled = false;
                show(errBox, '');
                show(warnBox, '');
                setBar(0);
            });
        });
    </script>
@endpush
