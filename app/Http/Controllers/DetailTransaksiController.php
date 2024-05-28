<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\Transaksi;
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

        $id_penyedia = $user->id_penyedia;
        $detailTransaksis = DetailTransaksi::whereHas('Paket', function ($query) use ($id_penyedia) {
            $query->where('id_penyedia', $id_penyedia);
        })->with('Paket.PenyediaJasa', 'Transaksi.Pengguna')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Detail transaksis retrieved successfully',
            'data' => $detailTransaksis,
        ], 200);
    }


    public function indexPengguna()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $id_pengguna = $user->id_pengguna;
        $detailTransaksis = DetailTransaksi::whereHas('Transaksi', function ($query) use ($id_pengguna) {
            $query->where('id_pengguna', $id_pengguna);
        })->with('Paket.PenyediaJasa', 'Transaksi.Pengguna')->get();

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

    public function tambahKeranjang(Request $request)
    {
        $id_pengguna = auth()->user()->id_pengguna;
        $validator = Validator::make($request->all(), [
            'id_paket' => 'required',
            'subtotal' => 'required',
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

        $transaksi = Transaksi::where('id_pengguna', $id_pengguna)
            ->whereNull('status_transaksi')
            ->first();

        if (!$transaksi) {
            $transaksi = Transaksi::create([
                'id_pengguna' => $id_pengguna,
                'status_transaksi' => null,
                'total_harga' => 0,
            ]);
        }

        $detailTransaksi = DetailTransaksi::create([
            'id_pengguna' => $id_pengguna,
            'id_transaksi' => $transaksi->id_transaksi,
            'id_paket' => $request->input('id_paket'),
            'subtotal' => $request->input('subtotal'),
            'tanggal_pelaksanaan' => $request->input('tanggal_pelaksanaan'),
            'jam_mulai' => $request->input('jam_mulai'),
            'jam_selesai' => $request->input('jam_selesai'),
            'status_berlangsung' => 'Keranjang',
        ]);

        $transaksi->total_harga += $request->input('subtotal');
        $transaksi->save();

        return response([
            'status' => 'success',
            'message' => 'Tambah Keranjang successfully',
            'data' => $detailTransaksi
        ], 201);
    }


    public function indexKeranjang()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }
    
        $id_pengguna = $user->id_pengguna;
        
        $detailTransaksis = DetailTransaksi::whereHas('Transaksi', function ($query) use ($id_pengguna) {
            $query->where('id_pengguna', $id_pengguna)
                  ->whereNull('status_transaksi');
        })->with('Paket.PenyediaJasa', 'Transaksi.Pengguna')->get();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Detail transaksis retrieved successfully',
            'data' => $detailTransaksis,
        ], 200);
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
