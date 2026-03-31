<div class="card monitor-card border-0 shadow-sm mb-3">
    <div class="card-header bg-body-tertiary border-bottom border-200 py-3 px-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h5 class="mb-1">Monitoring Overview</h5>
                <small class="text-muted">Quick summary of current CCTV workload and activity.</small>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                @php
                    $pill = [
                        '' => 'All',
                        'Open' => 'Open',
                        'In Progress' => 'In Progress',
                        'Fixed' => 'Fixed',
                        'Closed' => 'Closed',
                    ];
                @endphp

                @foreach ($pill as $val => $label)
                    <a class="btn btn-sm {{ request('status') === $val ? 'btn-primary' : 'btn-falcon-default' }}"
                        href="{{ route('concern.cctv.index', array_merge(request()->except('page'), ['status' => $val ?: null])) }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="monitor-tile status-open h-100">
                    <div class="tile-label">Open</div>
                    <div class="tile-value">{{ $statusCounts['Open'] ?? 0 }}</div>
                    <div class="tile-subtext">Needs attention</div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="monitor-tile status-progress h-100">
                    <div class="tile-label">In Progress</div>
                    <div class="tile-value">{{ $statusCounts['In Progress'] ?? 0 }}</div>
                    <div class="tile-subtext">Currently being worked on</div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="monitor-tile status-fixed h-100">
                    <div class="tile-label">Fixed</div>
                    <div class="tile-value">{{ $statusCounts['Fixed'] ?? 0 }}</div>
                    <div class="tile-subtext">Ready to close</div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="monitor-tile status-closed h-100">
                    <div class="tile-label">Closed</div>
                    <div class="tile-value">{{ $statusCounts['Closed'] ?? 0 }}</div>
                    <div class="tile-subtext">Completed concerns</div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="insight-card h-100">
                    <div class="insight-title">Top Issue Type</div>
                    <div class="insight-main">
                        <span>{{ $topIssue ?? '—' }}</span>
                        <strong>{{ $topIssueCount }}</strong>
                    </div>

                    <div class="insight-list">
                        @forelse ($issueCounts->take(5) as $k => $v)
                            <div class="insight-row">
                                <span>{{ $k }}</span>
                                <strong>{{ $v }}</strong>
                            </div>
                        @empty
                            <div class="text-muted small">No data yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="insight-card h-100">
                    <div class="insight-title">Most Used CCTV Part</div>
                    <div class="insight-main">
                        <span>{{ $topPart ?? '—' }}</span>
                        <strong>{{ $topPartCount }}</strong>
                    </div>

                    <div class="insight-list">
                        @forelse ($partCounts->take(5) as $k => $v)
                            <div class="insight-row">
                                <span>{{ $k }}</span>
                                <strong>{{ $v }}</strong>
                            </div>
                        @empty
                            <div class="text-muted small">No parts used yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="insight-card h-100">
                    <div class="insight-title">Top Assignee Workload</div>
                    <div class="insight-main">
                        <span>{{ $topAssignee ?? '—' }}</span>
                        <strong>{{ $topAssigneeCount }}</strong>
                    </div>

                    <div class="insight-list">
                        @forelse ($assigneeCounts->take(5) as $k => $v)
                            <div class="insight-row">
                                <span>{{ $k }}</span>
                                <strong>{{ $v }}</strong>
                            </div>
                        @empty
                            <div class="text-muted small">No assigned records yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
