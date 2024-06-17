<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaketController extends Controller
{
    public function index()
{
    $user = auth()->user();
    if (!$user) {
        return response()->json([
            'message' => 'User not authenticated.',
        ], 401);
    }

    $id_penyedia = $user->id_penyedia;
    $pakets = Paket::where('id_penyedia', $id_penyedia)->get();

    return response()->json([
        'status' => 'success',
        'message' => 'Pakets retrieved successfully',
        'data' => $pakets,
    ], 200);
}

    public function store(Request $request)
    {
        $id_penyedia = auth()->user()->id_penyedia;
        $validator = Validator::make($request->all(), [
            'nama_paket' => 'required|string',
            'harga_paket' => 'required|numeric',
            'isi_paket' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $paket = Paket::create([
            'id_penyedia' => $id_penyedia,
            'nama_paket' => $request->input('nama_paket'),
            'harga_paket' => $request->input('harga_paket'),
            'isi_paket' => $request->input('isi_paket'),
        ]);

        return response([
            'status' => 'success',
            'message' => 'Fasilitas Tambahan created successfully',
            'data' => $paket
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $paket = Paket::find($id);
    
        if (!$paket) {
            return response()->json([
                'message' => 'Paket not found.',
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'nama_paket' => 'required|string',
            'harga_paket' => 'required|numeric',
            'isi_paket' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
        $paket->nama_paket = $request->input('nama_paket');
        $paket->harga_paket = $request->input('harga_paket');
        $paket->isi_paket = $request->input('isi_paket');
        $paket->save();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Paket updated successfully',
            'data' => $paket,
        ], 200);
    }

    public function destroy($id)
    {
        $paket = Paket::find($id);
    
        if (!$paket) {
            return response()->json([
                'message' => 'Paket not found.',
            ], 404);
        }
    
        $paket->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Paket deleted successfully',
        ], 200);
    }
    
    public function getPaketsByPenyedia($id_penyedia)
    {
        $pakets = Paket::where('id_penyedia', $id_penyedia)->with('PenyediaJasa')->get();

        if ($pakets->isEmpty()) {
            return response()->json([
                'message' => 'No packages found for this provider.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pakets retrieved successfully',
            'data' => $pakets,
        ], 200);
    }
}
