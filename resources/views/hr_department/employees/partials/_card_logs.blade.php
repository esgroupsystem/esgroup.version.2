<div class="card mb-3 shadow-sm border-0">

    <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold text-900">
            <span class="fas fa-history text-primary me-2"></span>
            Employee Logs
        </h6>

        <span class="badge badge-subtle-primary rounded-pill">
            {{ $logs->total() }} total
        </span>
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

            $iconMap = [
                'created' => 'fa-user-plus',
                'updated_201_file' => 'fa-folder-open',
                'updated_profile' => 'fa-user-edit',
                'updated_status_details' => 'fa-clipboard-list',
                'uploaded_attachment' => 'fa-upload',
                'deleted_attachment' => 'fa-trash-alt',
                'added_history' => 'fa-plus-circle',
                'removed_history' => 'fa-minus-circle',
                'deleted_employee' => 'fa-user-times',
            ];

            $summaryFromMeta = function ($meta) use ($deptMap, $posMap) {
                $meta = is_array($meta) ? $meta : (json_decode($meta ?? '[]', true) ?: []);

                $labelize = function ($field) {
                    $field = str_replace('_id', '', $field);
                    return ucwords(str_replace('_', ' ', $field));
                };

                $formatValue = function ($value) {
                    if ($value === null || $value === '') {
                        return '—';
                    }

                    if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        try {
                            return \Carbon\Carbon::parse($value)->format('M d, Y');
                        } catch (\Throwable $e) {
                            return $value;
                        }
                    }

                    if (is_bool($value)) {
                        return $value ? 'Yes' : 'No';
                    }

                    return (string) $value;
                };

                if (empty($meta['changed']) || !is_array($meta['changed'])) {
                    return null;
                }

                $items = [];

                foreach ($meta['changed'] as $field => $change) {
                    $fromValue = $change['from'] ?? null;
                    $toValue = $change['to'] ?? null;

                    if ($field === 'department_id') {
                        $fromValue = $deptMap->get((int) $fromValue) ?? ($fromValue ?: '—');
                        $toValue = $deptMap->get((int) $toValue) ?? ($toValue ?: '—');
                    }

                    if ($field === 'position_id') {
                        $fromValue = $posMap->get((int) $fromValue) ?? ($fromValue ?: '—');
                        $toValue = $posMap->get((int) $toValue) ?? ($toValue ?: '—');
                    }

                    $items[] = [
                        'field' => $labelize($field),
                        'from' => $formatValue($fromValue),
                        'to' => $formatValue($toValue),
                    ];
                }

                if (empty($items)) {
                    return null;
                }

                return view('tickets.partials.log_changed_details', compact('items'))->render();
            };
        @endphp

        @forelse($logs as $log)
            @php
                $actor = $log->user->full_name ?? ($log->user->name ?? 'System');
                $actionLabel = $actionMap[$log->action] ?? ucwords(str_replace('_', ' ', $log->action));
                $badgeClass = $badgeMap[$log->action] ?? 'badge-subtle-secondary';
                $iconClass = $iconMap[$log->action] ?? 'fa-info-circle';
                $summary = $summaryFromMeta($log->meta);
            @endphp

            <div class="border-bottom px-3 py-3">
                <div class="row g-3 align-items-start">

                    <div class="col-auto">
                        <div class="avatar avatar-xl">
                            <div class="avatar-name rounded-circle bg-primary-subtle text-primary">
                                <span class="fas {{ $iconClass }}"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <span class="badge rounded-pill {{ $badgeClass }}">
                                {{ $actionLabel }}
                            </span>

                            <span class="fs--1 text-600">
                                by <strong class="text-800">{{ $actor }}</strong>
                            </span>
                        </div>

                        @if ($summary)
                            <div class="mt-2">
                                {!! $summary !!}
                            </div>
                        @else
                            <p class="mb-0 fs--1 text-500">
                                No additional changes recorded.
                            </p>
                        @endif
                    </div>

                    <div class="col-auto text-end">
                        <div class="fs--2 text-600">
                            {{ $log->created_at->format('M d, Y') }}
                        </div>
                        <div class="fs--2 text-500">
                            {{ $log->created_at->format('h:i A') }}
                        </div>
                    </div>

                </div>
            </div>

        @empty
            <div class="text-center py-5">
                <div class="avatar avatar-4xl mx-auto mb-3">
                    <div class="avatar-name rounded-circle bg-body-tertiary text-500">
                        <span class="fas fa-clipboard-list"></span>
                    </div>
                </div>

                <h6 class="text-700 mb-1">No logs available.</h6>
                <p class="fs--1 text-500 mb-0">
                    Employee activity history will appear here once changes are recorded.
                </p>
            </div>
        @endforelse

        @if ($logs->hasPages())
            <div class="px-3 py-3 bg-body-tertiary border-top">
                {{ $logs->links('pagination.custom') }}
            </div>
        @endif

    </div>
</div>
