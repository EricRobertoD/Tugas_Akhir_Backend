<?php

namespace App\Http\Controllers;

use App\Mail\forgotPassword;
use App\Models\Admin;
use App\Models\Pengguna;
use App\Models\PenyediaJasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $registerData = $request->all();

        $validate = Validator::make($registerData, [
            'nama_pengguna' => 'required',
            'email_pengguna' => 'required|string|email|max:255|unique:pengguna|unique:penyedia_jasa,email_penyedia',
            'password' => 'required',
            'nomor_telepon_pengguna' => 'required',
            'nomor_whatsapp_pengguna' => 'required',
            'alamat_pengguna' => 'required',
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

        $pengguna = Pengguna::create($registerData);
        return response()->json([
            'status' => 'success',
            'message' => 'Register Berhasil!.',
            'data' => $pengguna
        ], 200);
    }

    public function login(Request $request)
    {
        $loginData = $request->all();
        $email = $loginData['email'];

        $pengguna = Pengguna::where('email_pengguna', $email)->first();
        $penyedia = PenyediaJasa::where('email_penyedia', $email)->first();

        if ($pengguna && Auth::guard('pengguna')->attempt(['email_pengguna' => $email, 'password' => $loginData['password']])) {
            $user = Auth::guard('pengguna')->user();
            $token = $user->createToken('Authentication Token', ['pengguna'])->plainTextToken;

            return response([
                'message' => 'Authenticated as pengguna',
                'data' => [
                    'status' => 'success',
                    'User' => $user,
                    'token_type' => 'Bearer',
                    'access_token' => $token,
                ],
            ]);
        } elseif ($penyedia && Auth::guard('penyedia')->attempt(['email_penyedia' => $email, 'password' => $loginData['password']])) {
            $user = Auth::guard('penyedia')->user();
            $token = $user->createToken('Authentication Token', ['penyedia'])->plainTextToken;

            return response([
                'message' => 'Authenticated as penyedia',
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

    public function registerPenyedia(Request $request)
    {
        $registerData = $request->all();

        $validate = Validator::make($registerData, [
            'nama_penyedia' => 'required',
            'email_penyedia' => 'required|string|email|max:255|unique:penyedia_jasa|unique:pengguna,email_pengguna',
            'password' => 'required',
            'nomor_telepon_penyedia' => 'required',
            'nomor_whatsapp_penyedia' => 'required',
            'alamat_penyedia' => 'required',
            'nama_role' => 'required',
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

        $penyedia = PenyediaJasa::create($registerData);
        return response()->json([
            'status' => 'success',
            'message' => 'Register Berhasil!.',
            'data' => $penyedia
        ], 200);
    }


    public function logout(Request $request)
    {
        if (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            $user->tokens->each(function ($token) {
                $token->delete();
            });

            return response()->json([
                'message' => 'Logout Success',
                'user' => $user
            ], 200);
        } else {
            return response()->json([
                'message' => 'User not authenticated',
            ], 401);
        }
    }

    public function forgotPasswordPengguna(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email_pengguna' => 'required|email',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first(), 'errors' => $validate->errors()], 400);
        }

        $user = Pengguna::where('email_pengguna', $request->email_pengguna)->first();

        if (!$user) {
            $user = PenyediaJasa::where('email_penyedia', $request->email_pengguna)->first();
            if (!$user) {
                return response()->json([
                    'errors' => [
                        'email' => 'Email tidak ditemukan'
                    ]
                ], 404);
            }

            $token = $user->createToken('reset_password', ['reset_password'], now()->addMinutes(15))->plainTextToken;
            Mail::to($user->email_penyedia)->send(new forgotPassword($token, $user->nama_penyedia, $user->email_penyedia));
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengirimkan link untuk penyedia jasa.',
            ], 200);
        }

        $token = $user->createToken('reset_password', ['reset_password'], now()->addMinutes(15))->plainTextToken;

        Mail::to($user->email_pengguna)->send(new forgotPassword($token, $user->nama_pengguna, $user->email_pengguna));

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengirimkan link untuk pengguna.',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email_pengguna' => 'required|email',
            'password' => 'required',
            'token' => 'required',
        ]);
        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first(), 'errors' => $validate->errors()], 400);
        }

        $user = Pengguna::where('email_pengguna', $request->email_pengguna)->first();

        if (!$user) {
            $user = PenyediaJasa::where('email_penyedia', $request->email_pengguna)->first();
            if (!$user) {
                return response()->json([
                    'errors' => [
                        'email' => 'Email tidak ditemukan'
                    ]
                ], 404);
            }
        }

        $token = PersonalAccessToken::findToken($request->input('token'));

        if (!$token || $token->cant('reset_password') || $token->tokenable->email_pengguna !== $request->email_pengguna) {
            return response()->json([
                'errors' => [
                    'token' => 'Token tidak valid atau tidak cocok dengan email'
                ]
            ], 404);
        }

        $user->password = bcrypt($request->password);
        $user->save();
        $token->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Password Berhasil Diganti.',
        ], 200);
    }


    public function updatePenyedia(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $id_penyedia = $user->id_penyedia;
        $penyedia = PenyediaJasa::find($id_penyedia);

        if (!$penyedia) {
            return response()->json([
                'message' => 'Penyedia not found.',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama_penyedia' => 'required',
            'email_penyedia' => 'required|string|email|max:255|unique:penyedia_jasa,email_penyedia,' . $id_penyedia . ',id_penyedia',
            'nomor_telepon_penyedia' => 'required',
            'nomor_whatsapp_penyedia' => 'required',
            'alamat_penyedia' => 'required',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors();
            $response = [
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $errors->toArray()
            ];

            return response()->json($response, 400);
        }

        $penyedia->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Penyedia updated successfully',
            'data' => $penyedia,
        ], 200);
    }


    public function registerAdmin(Request $request)
    {
        $registerData = $request->all();

        $validate = Validator::make($registerData, [
            'nama_admin' => 'required',
            'email_admin' => 'required|string|email|max:255|unique:admin|unique:pengguna,email_pengguna|unique:penyedia_jasa,email_penyedia',
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

        $admin = Admin::create($registerData);
        return response()->json([
            'status' => 'success',
            'message' => 'Register Berhasil!.',
            'data' => $admin
        ], 200);
    }

    public function loginAdmin(Request $request)
    {
        $loginData = $request->all();
        $email = $loginData['email'];

        $admin = Admin::where('email_admin', $email)->first();

        if ($admin && Auth::guard('admin')->attempt(['email_admin' => $email, 'password' => $loginData['password']])) {
            $user = Auth::guard('admin')->user();
            $token = $user->createToken('Authentication Token', ['admin'])->plainTextToken;

            return response([
                'message' => 'Authenticated as admin',
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
}
