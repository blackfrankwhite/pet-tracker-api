<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Pet;
use App\Services\SMSService;
use App\Models\User;

class LocationController extends Controller
{
    public function __construct(SMSService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function store(Request $request, $token)
    {
        $validator = \Validator::make($request->all(), [
            'latitude' => 'required|string',
            'longitude' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    
        $pet = Pet::where('token', $request->token)->first();
        $user = User::where('id', $pet->user_id)->first();
        $sent = $this->smsService->sendSMS($user->mobile, " new location entry : {$request->latitude},{$request->longitude}");

        return $sent;

        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }
    
        $location = new Location([
            'pet_id' => $pet->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
        $location->save();
    
        return response()->json(['message' => 'Location saved successfully', 'location' => $location], 201);
    }
    
}
