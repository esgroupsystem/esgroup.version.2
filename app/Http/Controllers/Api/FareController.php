<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fare;
use Illuminate\Http\Request;

class FareController extends Controller
{
    public function index()
    {
        return response()->json(Fare::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_location' => 'required|string|max:255',
            'to_location'   => 'required|string|max:255',
            'fare'          => 'required|numeric|min:0',
        ]);

        $fare = Fare::create($data);

        return response()->json($fare, 201);
    }
}
