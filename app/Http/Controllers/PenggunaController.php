<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PenggunaController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $id_pengguna = $user->id_pengguna;
        $pengguna = Pengguna::where('id_pengguna', $id_pengguna)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'pengguna retrieved successfully',
            'data' => $pengguna,
        ], 200);
    }

    public function updateGambar(Request $request)
    {
        $id_pengguna = auth()->user()->id_pengguna;
        $pengguna = Pengguna::find($id_pengguna);

        if (!$pengguna) {
            return response()->json([
                'message' => 'Pengguna not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'gambar_pengguna' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        if ($request->hasFile('gambar_pengguna')) {
            $filenameWithExt = $request->file('gambar_pengguna')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar_pengguna')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $path = $request->file('gambar_pengguna')->storeAs('gambar', $fileNameToStore, 'public');

            if ($pengguna->gambar_pengguna !== 'noimage.jpg' && !is_null($pengguna->gambar_pengguna)) {
                Storage::disk('public')->delete('gambar/' . $pengguna->gambar_pengguna);
            }

            $pengguna->gambar_pengguna = $fileNameToStore;
        }

        $pengguna->save();

        return response()->json([
            'message' => 'Gambar updated successfully.',
            'data' => $pengguna,
        ], 200);
    }

    
    public function updatePengguna(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $id_pengguna = $user->id_pengguna;
        $pengguna = Pengguna::find($id_pengguna);

        if (!$pengguna) {
            return response()->json([
                'message' => 'pengguna not found.',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama_pengguna' => 'required',
            'email_pengguna' => 'required|string|email|max:255|unique:penyedia_jasa,email_penyedia|unique:pengguna,email_pengguna,' . $id_pengguna . ',id_pengguna',
            'nomor_telepon_pengguna' => 'required',
            'nomor_whatsapp_pengguna' => 'required',
            'alamat_pengguna' => 'required',
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

        $pengguna->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'pengguna updated successfully',
            'data' => $pengguna,
        ], 200);
    }
}
