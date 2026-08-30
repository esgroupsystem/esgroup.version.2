<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Api\CreateTripAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTripRequest;
use Illuminate\Http\JsonResponse;

final class TripController extends Controller
{
    public function __construct(
        private readonly CreateTripAction $createTrip,
    ) {}

    public function store(StoreTripRequest $request): JsonResponse
    {
        $trip = $this->createTrip->execute(
            $request->validated(),
            $request->user(),
        );

        return response()->json($trip, 201);
    }
}
