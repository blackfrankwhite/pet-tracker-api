<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function store(Request $request, $petId)
    {
        $request->validate([
            'latitude' => 'required|string',
            'longitude' => 'required|string',
        ]);

        $location = new Location([
            'pet_id' => $petId,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
        $location->save();

        return response()->json(['message' => 'Location saved successfully', 'location' => $location], 201);
    }
}
