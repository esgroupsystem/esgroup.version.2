<?php

namespace App\Services;

use App\Jobs\ProcessCrossChexRecord;
use Illuminate\Support\Facades\Log;

class CrossChexWebhookService
{
    public function processAttendanceRecords(array $payload): int
    {
        $records = data_get($payload, 'payload.list');

        if (! is_array($records)) {
            $singlePayload = data_get($payload, 'payload');

            if (is_array($singlePayload)) {
                $records = [$singlePayload];
            }
        }

        if (! is_array($records)) {
            $records = data_get($payload, 'list', []);
        }

        if (empty($records)) {
            Log::warning('CrossChex webhook received but no records found', [
                'payload' => $payload,
            ]);

            return 0;
        }

        foreach ($records as $record) {
            if (! is_array($record)) {
                continue;
            }

            ProcessCrossChexRecord::dispatch($record)->onQueue('crosschex');
        }

        return count($records);
    }
}
