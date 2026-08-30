<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Api\CreateFareAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFareRequest;
use App\Models\Fare;
use Illuminate\Http\JsonResponse;

final class FareController extends Controller
{
    public function __construct(
        private readonly CreateFareAction $createFare,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(Fare::query()->get());
    }

    public function store(StoreFareRequest $request): JsonResponse
    {
        $fare = $this->createFare->execute($request->validated());

        return response()->json($fare, 201);
    }
}
