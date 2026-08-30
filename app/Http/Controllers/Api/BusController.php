<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BusResource;
use App\Models\BusDetail;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class BusController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $buses = BusDetail::query()
            ->select(['id', 'name', 'body_number', 'plate_number'])
            ->orderBy('name')
            ->get();

        return BusResource::collection($buses);
    }
}
