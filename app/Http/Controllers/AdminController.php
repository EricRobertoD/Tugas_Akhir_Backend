<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Pengguna;
use App\Models\PenyediaJasa;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $id_admin = $user->id_admin;
        $admin = Admin::where('id_admin', $id_admin)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Admin retrieved successfully',
            'data' => $admin,
        ], 200);
    }

    public function updateStatusPengguna(Request $request, $id_pengguna)
    {
        $request->validate([
            'status_blokir' => 'required',
        ]);

        $pengguna = Pengguna::find($id_pengguna);

        if (!$pengguna) {
            return response()->json([
                'message' => 'Pengguna not found.',
            ], 404);
        }

        $pengguna->status_blokir = $request->status_blokir;
        $pengguna->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status blokir updated successfully for Pengguna.',
            'data' => $pengguna,
        ], 200);
    }

    public function updateStatusPenyedia(Request $request, $id_penyedia)
    {
        $request->validate([
            'status_blokir' => 'required',
        ]);

        $penyedia = PenyediaJasa::find($id_penyedia);

        if (!$penyedia) {
            return response()->json([
                'message' => 'Penyedia Jasa not found.',
            ], 404);
        }

        $penyedia->status_blokir = $request->status_blokir;
        $penyedia->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status blokir updated successfully for Penyedia Jasa.',
            'data' => $penyedia,
        ], 200);
    }
}
