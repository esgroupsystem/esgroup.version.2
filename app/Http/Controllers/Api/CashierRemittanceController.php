<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashierRemittance;
use Illuminate\Http\Request;

class CashierRemittanceController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'bus_number'        => 'required|string',
            'driver_name'       => 'required|string',
            'conductor_name'    => 'required|string',
            'dispatcher_name'   => 'required|string',
            'time_in'           => 'required|date_format:H:i',
            'time_out'          => 'required|date_format:H:i',
            'total_collection'  => 'required|numeric|min:0',
            'diesel'            => 'nullable|numeric|min:0',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['synced_at'] = now();

        $remittance = CashierRemittance::create($data);

        return response()->json($remittance, 201);
    }
}

