@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $attendanceSummaries = collect($summaries ?? []);

    $formatTime = function ($value): string {
        return $value ? Carbon::parse($value)->format('h:i A') : '—';
    };

    $formatDate = function ($value): string {
        return $value ? Carbon::parse($value)->format('M d, Y') : '—';
    };

    $formatDay = function ($value): string {
        return $value ? Carbon::parse($value)->format('D') : '—';
    };

    $normalizeStatus = function ($status): string {
        return Str::of($status ?: 'n/a')
            ->lower()
            ->replace([' ', '-'], '_')
            ->toString();
    };

    $formatStatus = function ($status): string {
        return Str::of($status ?: 'N/A')
            ->replace('_', ' ')
            ->title()
            ->toString();
    };

    $auditRows = $attendanceSummaries->map(function ($row) use ($normalizeStatus, $formatStatus) {
        $status = $normalizeStatus($row->attendance_status ?? null);

        $lateMinutes = (int) ($row->late_minutes ?? 0);
        $undertimeMinutes = (int) ($row->undertime_minutes ?? 0);
        $workedMinutes = (int) ($row->worked_minutes ?? 0);
        $overtimeMinutes = (int) ($row->overtime_minutes ?? 0);

        $hasSchedule = !empty($row->scheduled_time_in) && !empty($row->scheduled_time_out);
        $hasActualIn = !empty($row->actual_time_in);
        $hasActualOut = !empty($row->actual_time_out);

        $nonWorkingStatuses = [
            'rest_day',
            'rest_day_paid',
            'holiday',
            'paid_holiday',
            'leave',
            'paid_leave',
            'day_off',
        ];

        $expectedToLog = !in_array($status, $nonWorkingStatuses, true);

        $issues = [];

        if (in_array($status, ['absent', 'holiday_unpaid'], true)) {
            $issues[] = [
                'label' => 'Absent / Unpaid',
                'class' => 'danger',
            ];
        }

        if ($status === 'incomplete_log') {
            $issues[] = [
                'label' => 'Incomplete Log',
                'class' => 'danger',
            ];
        }

        if ($status === 'no_schedule') {
            $issues[] = [
                'label' => 'No Schedule',
                'class' => 'danger',
            ];
        }

        if ($expectedToLog && !$hasSchedule) {
            $issues[] = [
                'label' => 'Missing Schedule',
                'class' => 'danger',
            ];
        }

        if ($expectedToLog && (($hasActualIn && !$hasActualOut) || (!$hasActualIn && $hasActualOut))) {
            $issues[] = [
                'label' => 'Missing Pair Log',
                'class' => 'danger',
            ];
        }

        if ($lateMinutes > 0) {
            $issues[] = [
                'label' => "Late {$lateMinutes}m",
                'class' => 'warning',
            ];
        }

        if ($undertimeMinutes > 0) {
            $issues[] = [
                'label' => "Undertime {$undertimeMinutes}m",
                'class' => 'warning',
            ];
        }

        if ($status === 'half_day') {
            $issues[] = [
                'label' => 'Half Day',
                'class' => 'warning',
            ];
        }

        $hasDangerIssue = collect($issues)->contains(fn($issue) => $issue['class'] === 'danger');
        $hasWarningIssue = collect($issues)->contains(fn($issue) => $issue['class'] === 'warning');

        $severity = match (true) {
            $hasDangerIssue => 'danger',
            $hasWarningIssue => 'warning',
            in_array($status, ['holiday_worked', 'rest_day_worked'], true) => 'info',
            default => 'clean',
        };

        $statusClass = match (true) {
            in_array($status, ['present', 'adjusted_present'], true) => 'success',
            in_array($status, ['late', 'undertime', 'late_undertime', 'half_day'], true) => 'warning',
            in_array($status, ['absent', 'holiday_unpaid', 'incomplete_log', 'no_schedule'], true) => 'danger',
            in_array($status, ['rest_day', 'rest_day_worked'], true) => 'secondary',
            in_array($status, ['holiday', 'holiday_worked'], true) => 'info',
            $status === 'leave' => 'primary',
            default => 'dark',
        };

        return [
            'row' => $row,
            'status' => $status,
            'status_label' => $formatStatus($row->attendance_status ?? 'N/A'),
            'status_class' => $statusClass,
            'issues' => $issues,
            'severity' => $severity,
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'worked_hours' => $workedMinutes / 60,
            'overtime_hours' => $overtimeMinutes / 60,
        ];
    });

    $issueCount = $auditRows->filter(fn($audit) => count($audit['issues']) > 0)->count();
    $cleanCount = $auditRows->count() - $issueCount;

    $lateMinutesTotal = $attendanceSummaries->sum('late_minutes');
    $undertimeMinutesTotal = $attendanceSummaries->sum('undertime_minutes');
    $workedHoursTotal = $attendanceSummaries->sum('worked_minutes') / 60;
    $overtimeHoursTotal = $attendanceSummaries->sum('overtime_minutes') / 60;
    $payableDaysTotal = $attendanceSummaries->sum('payable_days');
    $payableHoursTotal = $attendanceSummaries->sum('payable_hours');
@endphp

@once
    <style>
        .payroll-audit-card {
            border-radius: 1rem;
            overflow: hidden;
        }

        .payroll-audit-summary {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .payroll-audit-chip {
            border: 1px solid var(--bs-border-color);
            border-radius: .85rem;
            padding: .65rem .85rem;
            background: var(--bs-body-bg);
            min-width: 135px;
        }

        .payroll-audit-chip small {
            display: block;
            color: var(--bs-secondary-color);
            font-size: .72rem;
            margin-bottom: .15rem;
        }

        .payroll-audit-chip strong {
            font-size: .95rem;
        }

        .payroll-audit-table {
            font-size: .82rem;
        }

        .payroll-audit-table thead th {
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: .68rem;
            color: var(--bs-secondary-color);
            background: var(--bs-tertiary-bg);
            border-bottom: 1px solid var(--bs-border-color);
            vertical-align: middle;
        }

        .payroll-audit-table tbody tr {
            border-left: 5px solid transparent;
        }

        .payroll-audit-table tbody tr.audit-row-danger {
            border-left-color: var(--bs-danger);
            background: rgba(var(--bs-danger-rgb), .045);
        }

        .payroll-audit-table tbody tr.audit-row-warning {
            border-left-color: var(--bs-warning);
            background: rgba(var(--bs-warning-rgb), .075);
        }

        .payroll-audit-table tbody tr.audit-row-info {
            border-left-color: var(--bs-info);
            background: rgba(var(--bs-info-rgb), .045);
        }

        .payroll-audit-table tbody tr.audit-row-clean {
            border-left-color: var(--bs-success);
        }

        .audit-date-box {
            min-width: 95px;
        }

        .audit-date-box strong {
            display: block;
            color: var(--bs-body-color);
        }

        .audit-date-box span {
            color: var(--bs-secondary-color);
            font-size: .75rem;
        }

        .audit-time-box {
            min-width: 165px;
        }

        .audit-time-line {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            padding: .15rem 0;
        }

        .audit-time-line span:first-child {
            color: var(--bs-secondary-color);
        }

        .audit-time-line span:last-child {
            font-weight: 600;
            color: var(--bs-body-color);
        }

        .audit-issue-list {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            min-width: 170px;
        }

        .audit-metric {
            min-width: 78px;
            text-align: center;
        }

        .audit-metric strong {
            display: block;
            font-size: .9rem;
        }

        .audit-metric span {
            display: block;
            color: var(--bs-secondary-color);
            font-size: .7rem;
        }

        .audit-remarks {
            min-width: 230px;
            max-width: 360px;
            white-space: normal;
        }

        .audit-empty-state {
            padding: 3rem 1rem;
            text-align: center;
            color: var(--bs-secondary-color);
        }
    </style>
@endonce

<div class="card shadow-sm border-0 payroll-audit-card">
    <div class="card-header bg-body-tertiary border-bottom">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
            <div>
                <h6 class="mb-1 fw-bold">
                    <i class="fas fa-triangle-exclamation me-2 text-warning"></i>
                    Attendance Audit Review
                </h6>

                <small class="text-muted">
                    Red rows need checking. Yellow rows have late, undertime, or partial-day issues.
                </small>
            </div>

            <div class="payroll-audit-summary">
                <div class="payroll-audit-chip">
                    <small>Total Days</small>
                    <strong>{{ number_format($auditRows->count()) }}</strong>
                </div>

                <div class="payroll-audit-chip">
                    <small>Clean Records</small>
                    <strong class="text-success">{{ number_format($cleanCount) }}</strong>
                </div>

                <div class="payroll-audit-chip">
                    <small>Needs Checking</small>
                    <strong class="{{ $issueCount > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($issueCount) }}
                    </strong>
                </div>

                <div class="payroll-audit-chip">
                    <small>Total Late</small>
                    <strong class="{{ $lateMinutesTotal > 0 ? 'text-warning' : 'text-success' }}">
                        {{ number_format($lateMinutesTotal) }} min
                    </strong>
                </div>

                <div class="payroll-audit-chip">
                    <small>Total Undertime</small>
                    <strong class="{{ $undertimeMinutesTotal > 0 ? 'text-warning' : 'text-success' }}">
                        {{ number_format($undertimeMinutesTotal) }} min
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 payroll-audit-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Issue Detected</th>
                        <th>Schedule</th>
                        <th>Actual Log</th>
                        <th class="text-center">Late</th>
                        <th class="text-center">UT</th>
                        <th class="text-center">Worked</th>
                        <th class="text-center">OT</th>
                        <th class="text-center">Payable</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($auditRows as $audit)
                        @php
                            $row = $audit['row'];

                            $rowClass = match ($audit['severity']) {
                                'danger' => 'audit-row-danger',
                                'warning' => 'audit-row-warning',
                                'info' => 'audit-row-info',
                                default => 'audit-row-clean',
                            };
                        @endphp

                        <tr class="{{ $rowClass }}">
                            <td>
                                <div class="audit-date-box">
                                    <strong>{{ $formatDate($row->work_date) }}</strong>
                                    <span>{{ $formatDay($row->work_date) }}</span>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-{{ $audit['status_class'] }}">
                                    {{ $audit['status_label'] }}
                                </span>
                            </td>

                            <td>
                                <div class="audit-issue-list">
                                    @forelse ($audit['issues'] as $issue)
                                        <span
                                            class="badge bg-{{ $issue['class'] }}-subtle text-{{ $issue['class'] }} border border-{{ $issue['class'] }}-subtle">
                                            {{ $issue['label'] }}
                                        </span>
                                    @empty
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            Clean
                                        </span>
                                    @endforelse
                                </div>
                            </td>

                            <td>
                                <div class="audit-time-box">
                                    <div class="audit-time-line">
                                        <span>In</span>
                                        <span>{{ $formatTime($row->scheduled_time_in) }}</span>
                                    </div>

                                    <div class="audit-time-line">
                                        <span>Out</span>
                                        <span>{{ $formatTime($row->scheduled_time_out) }}</span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="audit-time-box">
                                    <div class="audit-time-line">
                                        <span>In</span>
                                        <span>{{ $formatTime($row->actual_time_in) }}</span>
                                    </div>

                                    <div class="audit-time-line">
                                        <span>Out</span>
                                        <span>{{ $formatTime($row->actual_time_out) }}</span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="audit-metric">
                                    <strong class="{{ $audit['late_minutes'] > 0 ? 'text-warning' : 'text-muted' }}">
                                        {{ number_format($audit['late_minutes']) }}
                                    </strong>
                                    <span>min</span>
                                </div>
                            </td>

                            <td>
                                <div class="audit-metric">
                                    <strong
                                        class="{{ $audit['undertime_minutes'] > 0 ? 'text-warning' : 'text-muted' }}">
                                        {{ number_format($audit['undertime_minutes']) }}
                                    </strong>
                                    <span>min</span>
                                </div>
                            </td>

                            <td>
                                <div class="audit-metric">
                                    <strong>{{ number_format($audit['worked_hours'], 2) }}</strong>
                                    <span>hr</span>
                                </div>
                            </td>

                            <td>
                                <div class="audit-metric">
                                    <strong class="{{ $audit['overtime_hours'] > 0 ? 'text-info' : 'text-muted' }}">
                                        {{ number_format($audit['overtime_hours'], 2) }}
                                    </strong>
                                    <span>hr</span>
                                </div>
                            </td>

                            <td>
                                <div class="audit-metric">
                                    <strong>{{ number_format((float) ($row->payable_days ?? 0), 2) }}</strong>
                                    <span>{{ number_format((float) ($row->payable_hours ?? 0), 2) }} hr</span>
                                </div>
                            </td>

                            <td>
                                <div class="audit-remarks">
                                    {{ filled($row->remarks) ? $row->remarks : '—' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="audit-empty-state">
                                    <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                    No daily attendance summary found for this employee.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($auditRows->isNotEmpty())
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <th colspan="5" class="text-end">TOTAL</th>

                            <th class="text-center text-warning">
                                {{ number_format($lateMinutesTotal) }} min
                            </th>

                            <th class="text-center text-warning">
                                {{ number_format($undertimeMinutesTotal) }} min
                            </th>

                            <th class="text-center">
                                {{ number_format($workedHoursTotal, 2) }} hr
                            </th>

                            <th class="text-center text-info">
                                {{ number_format($overtimeHoursTotal, 2) }} hr
                            </th>

                            <th class="text-center">
                                <div>{{ number_format($payableDaysTotal, 2) }} day</div>
                                <small class="text-muted">
                                    {{ number_format($payableHoursTotal, 2) }} hr
                                </small>
                            </th>

                            <th></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
