<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'trip_id'       => 'required|exists:trips,id',
            'from_location' => 'required|string',
            'to_location'   => 'required|string',
            'fare'          => 'required|numeric|min:0',
            'issued_at'     => 'required|date',
        ]);

        $data['user_id'] = $request->user()->id;

        $ticket = Ticket::create($data);

        return response()->json($ticket, 201);
    }
}

