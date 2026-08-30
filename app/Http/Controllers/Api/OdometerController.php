<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Api\CreateOdometerSubmissionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOdometerSubmissionRequest;
use App\Http\Resources\Api\OdometerSubmissionResource;
use App\Models\BusDetail;
use App\Models\OdometerSubmission;
use Illuminate\Http\JsonResponse;

final class OdometerController extends Controller
{
    public function __construct(
        private readonly CreateOdometerSubmissionAction $createSubmission,
    ) {}

    public function store(StoreOdometerSubmissionRequest $request): JsonResponse
    {
        $submission = $this->createSubmission->execute(
            $request->validated(),
            $request->user(),
        );

        return response()->json([
            'message' => 'Submitted successfully',
            'data' => OdometerSubmissionResource::make($submission)->resolve($request),
        ]);
    }

    public function lastOdometer(BusDetail $busDetail): JsonResponse
    {
        $last = OdometerSubmission::query()
            ->where('bus_detail_id', $busDetail->getKey())
            ->latest()
            ->first();

        return response()->json([
            'last_odometer' => $last->new_odometer ?? 0,
        ]);
    }
}
