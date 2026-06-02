<?php

namespace App\Http\Controllers;

use App\Services\CrossChexWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BiometricsWebhookController extends Controller
{
    public function __construct(
        private readonly CrossChexWebhookService $crossChexWebhookService
    ) {}

    public function receive(Request $request): JsonResponse
    {
        Log::info('CrossChex webhook hit', [
            'ip' => $request->ip(),
            'headers' => [
                'content_type' => $request->header('content-type'),
                'x_crosschex_password_exists' => $request->hasHeader('x-crosschex-password'),
                'authorize_key_exists' => $request->hasHeader('authorize-key'),
            ],
            'payload' => $request->all(),
        ]);

        $providedPassword =
            $request->header('x-crosschex-password')
            ?? $request->header('authorize-key')
            ?? $request->input('password');

        if ($providedPassword !== config('services.crosschex.webhook_password')) {
            Log::warning('Unauthorized CrossChex webhook attempt', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'code' => 403,
                'msg' => 'Unauthorized',
            ], 403);
        }

        $processed = $this->crossChexWebhookService->processAttendanceRecords($request->all());

        return response()->json([
            'code' => 200,
            'msg' => 'success',
            'queued' => $processed,
        ]);
    }
}
