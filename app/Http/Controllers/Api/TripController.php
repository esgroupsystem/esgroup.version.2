<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'trip_code'   => 'required|string|unique:trips,trip_code',
            'bus_number'  => 'required|string',
            'driver_name' => 'required|string',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['started_at'] = now();

        $trip = Trip::create($data);

        return response()->json($trip, 201);
    }
}


