<?php

namespace App\Services;

class CrossChexServiceFactory
{
    public function make(string $account): CrossChexService
    {
        if (! isset($this->configuredAccounts()[$account])) {
            throw new \RuntimeException("CrossChex account [{$account}] is not fully configured.");
        }

        return new CrossChexService($account);
    }

    /**
     * Return only accounts that have a usable URL, API key and API secret.
     * Secrets are intentionally never returned to the browser/view layer.
     */
    public function configuredAccounts(): array
    {
        $accounts = config('services.crosschex.accounts', []);
        $configured = [];

        foreach ($accounts as $key => $account) {
            if (! is_array($account)) {
                continue;
            }

            $url = trim((string) ($account['url'] ?? ''));
            $apiKey = trim((string) ($account['key'] ?? ''));
            $secret = trim((string) ($account['secret'] ?? ''));

            if ($url === '' || $apiKey === '' || $secret === '') {
                continue;
            }

            $configured[(string) $key] = [
                'key' => (string) $key,
                'name' => trim((string) ($account['name'] ?? $key)) ?: (string) $key,
            ];
        }

        return $configured;
    }

    public function accounts(): array
    {
        return array_keys($this->configuredAccounts());
    }
}
