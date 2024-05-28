<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        'bukti_bayar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 400);
    }

    if ($request->hasFile('bukti_bayar')) {
        $filenameWithExt = $request->file('bukti_bayar')->getClientOriginalName();
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        $extension = $request->file('bukti_bayar')->getClientOriginalExtension();
        $fileNameToStore = $filename . '_' . time() . '.' . $extension;
        $path = $request->file('bukti_bayar')->storeAs('gambar', $fileNameToStore, 'public');

        if ($transaksi->bukti_bayar !== 'noimage.jpg' && !is_null($transaksi->bukti_bayar)) {
            Storage::disk('public')->delete('gambar/' . $transaksi->bukti_bayar);
        }

        $transaksi->bukti_bayar = $fileNameToStore;
    }

    $transaksi->status_transaksi = $request->input('status_transaksi');
    $transaksi->tanggal_pemesanan = Carbon::today()->format('Y-m-d');
    $transaksi->save();

    // Update related detail_transaksi entries
    $detailTransaksis = $transaksi->detailTransaksi;
    foreach ($detailTransaksis as $detailTransaksi) {
        $detailTransaksi->status_penyedia_jasa = 'Sedang Menghubungkan';
        $detailTransaksi->save();
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Transaksi updated successfully',
        'data' => $transaksi,
    ], 200);
}

}
