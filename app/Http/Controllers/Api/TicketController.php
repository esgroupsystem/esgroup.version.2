<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Api\CreateTicketAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTicketRequest;
use Illuminate\Http\JsonResponse;

final class TicketController extends Controller
{
    public function __construct(
        private readonly CreateTicketAction $createTicket,
    ) {}

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $this->createTicket->execute(
            $request->validated(),
            $request->user(),
        );

        return response()->json($ticket, 201);
    }
}
