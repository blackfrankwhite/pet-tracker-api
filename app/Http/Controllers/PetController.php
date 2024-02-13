<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PetController extends Controller
{
    public function index()
    {
        $pets = Pet::where('user_id', Auth::id())->get()->map(function ($pet) {
            // Generate a URL to the controller action that serves the QR code
            if ($pet->qr_code) {
                $pet->qr_code_url = route('pets.qrcode', ['id' => $pet->id]);
            }
            return $pet;
        });
        return response()->json($pets);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'breed' => 'required|string|max:255',
            'birth_year' => 'required|digits:4|integer|min:1900|max:' . date('Y'),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    
        $pet = new Pet();
        $pet->user_id = Auth::id();
        $pet->token = \Str::random(10);
        $pet->name = $request->name;
        $pet->breed = $request->breed;
        $pet->birth_year = $request->birth_year;
    
        if ($request->hasFile('image')) {
            $imageFileName = \Str::random(40) . '.' . $request->file('image')->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('pets/images', $imageFileName);
            $pet->image = $path;
        }
    
        $pet->save();
        $frontEndUrl = env('FRONT_END_URL', 'http://localhost:3000');
        $qrCodeUrl = "{$frontEndUrl}/pet-location/{$pet->token}";
        $qrCodeContent = QrCode::size(300)->generate($qrCodeUrl);
            
        $qrCodeFileName = \Str::random(40) . '.svg';
        $qrCodePath = 'pets/qrcodes/' . $qrCodeFileName;
        Storage::disk('local')->put($qrCodePath, $qrCodeContent);
    
        $pet->qr_code = $qrCodePath;
        $pet->save();
    
        return response()->json(['message' => 'Pet created successfully', 'pet' => $pet], 201);
    } 

    public function show($id)
    {
        $pet = Pet::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        return response()->json($pet);
    }

    public function update(Request $request, $id)
    {
        $pet = Pet::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'breed' => 'string|max:255',
            'birth_year' => 'digits:4|integer|min:1900|max:' . date('Y'),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $pet->update($request->only(['name', 'breed', 'birth_year']));

        if ($request->hasFile('image')) {
            if ($pet->image) {
                Storage::delete($pet->image);
            }

            $path = $request->file('image')->store('pets/images', 'public');
            $pet->image = Storage::url($path);
        }

        return response()->json(['message' => 'Pet updated successfully', 'pet' => $pet]);
    }

    public function destroy($id)
    {
        $pet = Pet::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        // Delete image if exists
        if ($pet->image) {
            Storage::delete($pet->image);
        }

        $pet->delete();

        return response()->json(['message' => 'Pet deleted successfully']);
    }

    public function getQrCode($id)
    {
        $pet = Pet::where('id', $id)->where('user_id', Auth::id())->first();
    
        if (!$pet || !$pet->qr_code) {
            return response()->json(['message' => 'Pet or QR code not found'], 404);
        }
    
        $path = storage_path('app/' . $pet->qr_code);
    
        if (!file_exists($path)) {
            return response()->json(['message' => 'QR code file not found'], 404);
        }
    
        return response()->file($path);
    }

    public function getLocations(Request $request, $id)
    {
        $pet = Pet::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        $query = $pet->locations()->orderBy('created_at', 'desc');

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $locations = $query->get();

        if ($locations->isEmpty()) {
            return [];
        }

        return response()->json($locations);
    }
}
