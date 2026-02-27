<div class="card mb-3 shadow-sm">
    <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-history mono-icon me-2"></i> Employee Logs
        </h6>
        <span class="small-muted">{{ $logs->total() }} total</span>
    </div>

    <div class="card-body p-0">
        @php
            $actionMap = [
                'created' => 'Created Employee',
                'updated_201_file' => 'Updated 201 File',
                'updated_profile' => 'Updated Profile',
                'updated_status_details' => 'Updated Status Details',
                'uploaded_attachment' => 'Uploaded Attachment',
                'deleted_attachment' => 'Deleted Attachment',
                'added_history' => 'Added History',
                'removed_history' => 'Removed History',
                'deleted_employee' => 'Deleted Employee',
            ];

            $badgeMap = [
                'created' => 'badge-subtle-success',
                'updated_201_file' => 'badge-subtle-warning',
                'updated_profile' => 'badge-subtle-primary',
                'updated_status_details' => 'badge-subtle-warning',
                'uploaded_attachment' => 'badge-subtle-info',
                'deleted_attachment' => 'badge-subtle-danger',
                'added_history' => 'badge-subtle-info',
                'removed_history' => 'badge-subtle-danger',
                'deleted_employee' => 'badge-subtle-danger',
            ];

            $summaryFromMeta = function ($meta) use ($deptMap, $posMap) {
                $meta = is_array($meta) ? $meta : (json_decode($meta ?? '[]', true) ?: []);

                $labelize = function ($f) {
                    $f = str_replace('_id', '', $f);
                    return ucwords(str_replace('_', ' ', $f));
                };

                $fmt = function ($v) {
                    if ($v === null || $v === '') return '—';

                    if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                        try { return \Carbon\Carbon::parse($v)->format('M d, Y'); } catch (\Throwable $e) {}
                    }

                    return is_bool($v) ? ($v ? 'Yes' : 'No') : (string) $v;
                };

                if (!empty($meta['changed']) && is_array($meta['changed'])) {
                    $items = [];

                    foreach ($meta['changed'] as $field => $c) {
                        $fromVal = $c['from'] ?? null;
                        $toVal   = $c['to'] ?? null;

                        if ($field === 'department_id') {
                            $fromVal = $deptMap->get((int) $fromVal) ?? ($fromVal ?: '—');
                            $toVal   = $deptMap->get((int) $toVal) ?? ($toVal ?: '—');
                        }

                        if ($field === 'position_id') {
                            $fromVal = $posMap->get((int) $fromVal) ?? ($fromVal ?: '—');
                            $toVal   = $posMap->get((int) $toVal) ?? ($toVal ?: '—');
                        }

                        $items[] = [
                            'field' => $labelize($field),
                            'from'  => $fmt($fromVal),
                            'to'    => $fmt($toVal),
                        ];
                    }

                    if (!$items) return null;

                    // You already use this partial in your system:
                    return view('tickets.partials.log_changed_details', compact('items'))->render();
                }

                return null;
            };
        @endphp

        @forelse($logs as $log)
            @php
                $actor = $log->user->full_name ?? ($log->user->name ?? 'System');
                $actionLabel = $actionMap[$log->action] ?? ucwords(str_replace('_', ' ', $log->action));
                $badge = $badgeMap[$log->action] ?? 'badge-subtle-secondary';
                $summary = $summaryFromMeta($log->meta);
            @endphp

            <div class="px-3 py-2 border-bottom">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="pe-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge rounded-pill {{ $badge }}">{{ $actionLabel }}</span>
                            <span class="small-muted">By: <strong>{{ $actor }}</strong></span>
                        </div>

                        @if ($summary)
                            <div class="mt-1">{!! $summary !!}</div>
                        @endif
                    </div>

                    <div class="small-muted text-end">
                        <div>{{ $log->created_at->format('M d, Y') }}</div>
                        <div>{{ $log->created_at->format('h:i A') }}</div>
                    </div>
                </div>
            </div>

        @empty
            <div class="p-3 text-muted">No logs available.</div>
        @endforelse

        @if ($logs->hasPages())
            <div class="p-3 border-top">
                {{ $logs->links('pagination.custom') }}
            </div>
        @endif
    </div>
</div>