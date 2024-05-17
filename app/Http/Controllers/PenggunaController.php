<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;

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
            'message' => 'Penyedia retrieved successfully',
            'data' => $pengguna,
        ], 200);
    }
}
