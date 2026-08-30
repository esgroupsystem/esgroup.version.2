<?php

declare(strict_types=1);

return [
    'trusted_proxies' => env('TRUSTED_PROXIES')
        ? array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES')))))
        : null,

    'uploads' => [
        'employee_attachment_max_kb' => (int) env('EMPLOYEE_ATTACHMENT_MAX_KB', 10240),
        'job_order_max_kb' => (int) env('JOB_ORDER_ATTACHMENT_MAX_KB', 51200),
        'max_upload_files_per_request' => (int) env('MAX_UPLOAD_FILES_PER_REQUEST', 10),
        'max_upload_total_kb' => (int) env('MAX_UPLOAD_TOTAL_KB', 102400),
    ],
];
