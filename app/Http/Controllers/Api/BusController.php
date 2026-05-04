<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusDetail;

class BusController extends Controller
{
    public function index()
    {
        $buses = BusDetail::select('id', 'name', 'body_number', 'plate_number')
            ->orderBy('name')
            ->get();

        return response()->json($buses);
    }
}
