<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetailTransaksiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $detailTransaksis = DetailTransaksi::with('Paket')->with('Transaksi')->with('Paket.PenyediaJasa')->with('Transaksi.Pengguna')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Detail transaksis retrieved successfully',
            'data' => $detailTransaksis,
        ], 200);
    }

    public function store(Request $request)
    {
        $id_pengguna = auth()->user()->id_pengguna;
        $validator = Validator::make($request->all(), [
            'id_transaksi' => 'required',
            'id_paket' => 'required',
            'status_penyedia_jasa' => 'required',
            'subtotal' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $detailTransaksi = DetailTransaksi::create([
            'id_transaksi' => $request->input('id_transaksi'),
            'id_paket' => $request->input('id_paket'),
            'status_penyedia_jasa' => $request->input('status_penyedia_jasa'),
            'subtotal' => $request->input('subtotal'),
        ]);

        return response([
            'status' => 'success',
            'message' => 'Detail transaksi created successfully',
            'data' => $detailTransaksi
        ], 201);
    }

    
    public function updateStatus(Request $request, $id)
    {
        $transaksi = DetailTransaksi::find($id);
    
        if (!$transaksi) {
            return response()->json([
                'message' => 'Detail Transaksi not found.',
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'status_penyedia_jasa' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
        $transaksi->status_penyedia_jasa = $request->input('status_penyedia_jasa');
        $transaksi->save();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Detail Transaksi updated successfully',
            'data' => $transaksi,
        ], 200);
    }
}
