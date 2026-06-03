<?php

namespace App\Services;

class CrossChexServiceFactory
{
    public function make(string $account): CrossChexService
    {
        return new CrossChexService($account);
    }

    public function accounts(): array
    {
        return array_keys(config('services.crosschex.accounts', []));
    }
}
