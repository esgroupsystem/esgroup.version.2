<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CrossChexService
{
    private string $account;

    private string $name;

    private string $url;

    private string $key;

    private string $secret;

    public function __construct(string $account)
    {
        $accounts = config('services.crosschex.accounts', []);
        $config = $accounts[$account] ?? null;

        if (! is_array($config)) {
            throw new \RuntimeException("CrossChex account [{$account}] is not configured.");
        }

        $this->account = $account;
        $this->name = (string) ($config['name'] ?? $account);
        $this->url = rtrim((string) ($config['url'] ?? ''), '/');
        $this->key = (string) ($config['key'] ?? '');
        $this->secret = (string) ($config['secret'] ?? '');
    }

    public function account(): string
    {
        return $this->account;
    }

    public function accountName(): string
    {
        return $this->name;
    }

    private function endpoint(): string
    {
        return $this->url === '' ? '' : $this->url.'/';
    }

    private function tokenCacheKey(): string
    {
        return "crosschex_token:{$this->account}";
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
            throw new \RuntimeException("CrossChex URL is not configured for account [{$this->account}].");
        }

        if (! filter_var($this->url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException("CrossChex URL is invalid for account [{$this->account}]: {$this->url}");
        }

        if (! $this->key || ! $this->secret) {
            throw new \RuntimeException("CrossChex API key/secret not configured for account [{$this->account}].");
        }
    }

    public function clearToken(): void
    {
        Cache::forget($this->tokenCacheKey());
    }

    public function token(bool $forceRefresh = false): string
    {
        $this->assertConfigured();

        if ($forceRefresh) {
            $this->clearToken();
        }

        return Cache::remember($this->tokenCacheKey(), now()->addMinutes(50), function (): string {
            $body = [
                'header' => $this->baseHeader('authorize.token', 'token'),
                'payload' => [
                    'api_key' => $this->key,
                    'api_secret' => $this->secret,
                ],
            ];

            try {
                $response = Http::timeout(30)
                    ->connectTimeout(15)
                    ->acceptJson()
                    ->asJson()
                    ->post($this->endpoint(), $body);

                if (! $response->successful()) {
                    throw new \RuntimeException(
                        'CrossChex token HTTP failed: '.$response->status().' - '.$response->body()
                    );
                }

                $json = $response->json();
            } catch (RequestException $e) {
                throw new \RuntimeException(
                    'CrossChex token HTTP failed: '.($e->response?->body() ?? $e->getMessage()),
                    0,
                    $e
                );
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
            $response = Http::timeout(60)
                ->connectTimeout(15)
                ->acceptJson()
                ->asJson()
                ->post($this->endpoint(), $body);

            if ($response->status() === 401 || $response->status() === 403) {
                $this->clearToken();

                $body['authorize']['token'] = $this->token(true);

                $response = Http::timeout(60)
                    ->connectTimeout(15)
                    ->acceptJson()
                    ->asJson()
                    ->post($this->endpoint(), $body);
            }

            if (! $response->successful()) {
                throw new \RuntimeException(
                    'CrossChex getrecord HTTP failed: '.$response->status().' - '.$response->body()
                );
            }

            $json = $response->json();
        } catch (RequestException $e) {
            throw new \RuntimeException(
                'CrossChex getrecord HTTP failed: '.($e->response?->body() ?? $e->getMessage()),
                0,
                $e
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException('CrossChex getrecord request failed: '.$e->getMessage(), 0, $e);
        }

        if (! is_array($json)) {
            throw new \RuntimeException('CrossChex getrecord invalid JSON response.');
        }

        return $json;
    }
}
