<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OwnerController extends Controller
{
    public function registerOwner(Request $request)
    {
        $registerData = $request->all();

        $validate = Validator::make($registerData, [
            'nama_owner' => 'required',
            'email_owner' => 'required|string|email|max:255|unique:owner|unique:pengguna,email_pengguna|unique:penyedia_jasa,email_penyedia',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors();
            $response = [
                'status' => 'error',
                'message' => 'Registrasi gagal. Silakan periksa semua bagian yang ditandai.',
                'errors' => $errors->toArray()
            ];

            return response()->json($response, 400);
        }

        $registerData['password'] = bcrypt($registerData['password']);

        $owner = Owner::create($registerData);
        return response()->json([
            'status' => 'success',
            'message' => 'Register Berhasil!.',
            'data' => $owner
        ], 200);
    }

    
    public function loginOwner(Request $request)
    {
        $loginData = $request->all();
        $email = $loginData['email'];

        $owner = Owner::where('email_owner', $email)->first();

        if ($owner && Auth::guard('owner')->attempt(['email_owner' => $email, 'password' => $loginData['password']])) {
            $user = Auth::guard('owner')->user();
            $token = $user->createToken('Authentication Token', ['owner'])->plainTextToken;

            return response([
                'message' => 'Authenticated as owner',
                'data' => [
                    'status' => 'success',
                    'User' => $user,
                    'token_type' => 'Bearer',
                    'access_token' => $token,
                ],
            ]);
        }

        return response(['message' => 'Invalid credentials'], 401);
    }

    
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $id_owner = $user->id_owner;
        $owner = Owner::where('id_owner', $id_owner)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'owner retrieved successfully',
            'data' => $owner,
        ], 200);
    }
}
