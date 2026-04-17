<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CrossChexService
{
    private string $url;

    private string $key;

    private string $secret;

    public function __construct()
    {
        $this->url = rtrim((string) config('services.crosschex.url'), '/');
        $this->key = (string) config('services.crosschex.key');
        $this->secret = (string) config('services.crosschex.secret');
    }

    private function endpoint(): string
    {
        if ($this->url === '') {
            return '';
        }

        return $this->url.'/';
    }

    private function baseHeader(string $namespace, string $action): array
    {
        return [
            'nameSpace' => $namespace,
            'nameAction' => $action,
            'version' => '1.0',
            'requestId' => (string) Str::uuid(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    private function assertConfigured(): void
    {
        if (! $this->url) {
            throw new \RuntimeException('CrossChex URL is not configured. Check services.crosschex.url');
        }

        if (! filter_var($this->url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('CrossChex URL is invalid: '.$this->url);
        }

        if (! $this->key || ! $this->secret) {
            throw new \RuntimeException('CrossChex API key/secret not configured. Check services.crosschex.key/secret');
        }
    }

    public function clearToken(): void
    {
        Cache::forget('crosschex_token');
    }

    public function token(bool $forceRefresh = false): string
    {
        $this->assertConfigured();

        if ($forceRefresh) {
            $this->clearToken();
        }

        return Cache::remember('crosschex_token', now()->addMinutes(50), function () {
            $body = [
                'header' => $this->baseHeader('authorize.token', 'token'),
                'payload' => [
                    'api_key' => $this->key,
                    'api_secret' => $this->secret,
                ],
            ];

            try {
                $res = Http::timeout(30)
                    ->connectTimeout(15)
                    ->acceptJson()
                    ->asJson()
                    ->post($this->endpoint(), $body);

                if (! $res->successful()) {
                    throw new \RuntimeException('CrossChex token HTTP failed: '.$res->status().' - '.$res->body());
                }

                $json = $res->json();
            } catch (RequestException $e) {
                throw new \RuntimeException('CrossChex token HTTP failed: '.($e->response?->body() ?? $e->getMessage()), 0, $e);
            } catch (\Throwable $e) {
                throw new \RuntimeException('CrossChex token request failed: '.$e->getMessage(), 0, $e);
            }

            if (! is_array($json)) {
                throw new \RuntimeException('CrossChex token invalid JSON response.');
            }

            if (data_get($json, 'header.name') === 'Exception') {
                throw new \RuntimeException(
                    'CrossChex token error: '.data_get($json, 'payload.type').' - '.data_get($json, 'payload.message')
                );
            }

            $token = data_get($json, 'payload.token');

            if (! $token) {
                throw new \RuntimeException('CrossChex token missing: '.json_encode($json));
            }

            return (string) $token;
        });
    }

    public function getAttendanceRecords(string $from, string $to, int $page = 1, int $perPage = 200): array
    {
        $this->assertConfigured();

        $begin = Carbon::parse($from, config('app.timezone', 'Asia/Manila'))
            ->utc()
            ->toIso8601String();

        $end = Carbon::parse($to, config('app.timezone', 'Asia/Manila'))
            ->utc()
            ->toIso8601String();

        $body = [
            'header' => $this->baseHeader('attendance.record', 'getrecord'),
            'authorize' => [
                'type' => 'token',
                'token' => $this->token(),
            ],
            'payload' => [
                'begin_time' => $begin,
                'end_time' => $end,
                'order' => 'asc',
                'page' => (int) $page,
                'per_page' => (int) $perPage,
            ],
        ];

        try {
            $res = Http::timeout(60)
                ->connectTimeout(15)
                ->acceptJson()
                ->asJson()
                ->post($this->endpoint(), $body);

            if ($res->status() === 401 || $res->status() === 403) {
                $this->clearToken();

                $body['authorize']['token'] = $this->token(true);

                $res = Http::timeout(60)
                    ->connectTimeout(15)
                    ->acceptJson()
                    ->asJson()
                    ->post($this->endpoint(), $body);
            }

            if (! $res->successful()) {
                throw new \RuntimeException('CrossChex getrecord HTTP failed: '.$res->status().' - '.$res->body());
            }

            $json = $res->json();
        } catch (RequestException $e) {
            throw new \RuntimeException('CrossChex getrecord HTTP failed: '.($e->response?->body() ?? $e->getMessage()), 0, $e);
        } catch (\Throwable $e) {
            throw new \RuntimeException('CrossChex getrecord request failed: '.$e->getMessage(), 0, $e);
        }

        if (! is_array($json)) {
            throw new \RuntimeException('CrossChex getrecord invalid JSON response.');
        }

        return $json;
    }
}
