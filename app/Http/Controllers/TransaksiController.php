<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
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
        $transaksi = Transaksi::where('id_pengguna', $id_pengguna)->with('DetailTransaksi')->with('Pengguna')->with('DetailTransaksi.Paket')->with('DetailTransaksi.Paket.PenyediaJasa')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi retrieved successfully',
            'data' => $transaksi,
        ], 200);
    }

    public function store(Request $request)
    {
        $id_pengguna = auth()->user()->id_pengguna;
        $validator = Validator::make($request->all(), [
            'total_harga' => 'required',
            'tanggal_pelaksanaan' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $transaksi = Transaksi::create([
            'id_pengguna' => $id_pengguna,
            'total_harga' => $request->input('total_harga'),
            'tanggal_pelaksanaan' => $request->input('tanggal_pelaksanaan'),
            'jam_mulai' => $request->input('jam_mulai'),
            'jam_selesai' => $request->input('jam_selesai'),
        ]);

        return response([
            'status' => 'success',
            'message' => 'Transaksi created successfully',
            'data' => $transaksi
        ], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json([
                'message' => 'Transaksi not found.',
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'status_transaksi' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
        $transaksi->status_transaksi = $request->input('status_transaksi');
        $transaksi->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi updated successfully',
            'data' => $transaksi,
        ], 200);
    }

}
