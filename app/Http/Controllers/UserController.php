<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function get(Request $request)
    {
        $user = $request->user();

        return [
            'email' => $user['email'],
            'mobile' => $user['mobile'],
            'comment' => $user['comment'],
            'name' => $user['name'],
        ];
    }
    
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile' => 'required|string|min:8',
            'comment' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = $request->user();

        return User::where('id', $user['id'])->update([
            'email' => $request->email,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'comment' => $request->comment,
        ]);
    }
}
