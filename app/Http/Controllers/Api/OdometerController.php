<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OdometerSubmission;
use Illuminate\Http\Request;

class OdometerController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'bus_detail_id' => 'required|exists:bus_details,id',
            'new_odometer' => 'required|numeric',
            'diesel_consumption' => 'required|numeric|min:0',
            'driver_name' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
        ]);

        $submission = OdometerSubmission::create([
            'user_id' => $request->user()->id,
            'bus_detail_id' => $request->bus_detail_id,
            'new_odometer' => $request->new_odometer,
            'diesel_consumption' => $request->diesel_consumption,
            'driver_name' => $request->driver_name,
            'date' => $request->date,
            'time' => $request->time,
        ]);

        return response()->json([
            'message' => 'Submitted successfully',
            'data' => $submission,
        ]);
    }

    public function lastOdometer($busDetail)
    {
        $last = OdometerSubmission::where('bus_detail_id', $busDetail)
            ->latest()
            ->first();

        return response()->json([
            'last_odometer' => $last?->new_odometer ?? 0,
        ]);
    }
}
