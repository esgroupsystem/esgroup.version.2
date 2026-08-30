<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Api\CreateCashierRemittanceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCashierRemittanceRequest;
use Illuminate\Http\JsonResponse;

final class CashierRemittanceController extends Controller
{
    public function __construct(
        private readonly CreateCashierRemittanceAction $createRemittance,
    ) {}

    public function store(StoreCashierRemittanceRequest $request): JsonResponse
    {
        $remittance = $this->createRemittance->execute(
            $request->validated(),
            $request->user(),
        );

        return response()->json($remittance, 201);
    }
}
