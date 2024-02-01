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
        $pets = Pet::where('user_id', Auth::id())->get();
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
        $pet->name = $request->name;
        $pet->breed = $request->breed;
        $pet->birth_year = $request->birth_year;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('private/pets/images');
            $pet->image = $path;
        }

        $pet->save();

        $qrCodeDirectory = 'private/pets/qrcodes';
        $qrCodeFileName = $pet->id . '.svg';
        $qrCodePath = $qrCodeDirectory . '/' . $qrCodeFileName;

        $qrCodeContent = QrCode::size(300)->generate(url('/api/pets/' . $pet->id));

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
            // Delete old image if exists
            if ($pet->image) {
                Storage::delete($pet->image);
            }

            $path = $request->file('image')->store('private/pets/images');
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
}
