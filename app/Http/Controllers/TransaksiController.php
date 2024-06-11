<?php

namespace App\Http\Controllers;

use App\Models\Saldo;
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
            'tanggal_pelaksanaan' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $tanggal_pelaksanaan = $request->input('tanggal_pelaksanaan');
        $year = date('Y', strtotime($tanggal_pelaksanaan));
        $month = date('m', strtotime($tanggal_pelaksanaan));
        $day = date('d', strtotime($tanggal_pelaksanaan));

        $lastTransaksi = Transaksi::whereDate('tanggal_pelaksanaan', $tanggal_pelaksanaan)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastTransaksi) {
            $lastInvoiceNumber = intval(substr($lastTransaksi->invoice, -3));
            $newInvoiceNumber = str_pad($lastInvoiceNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newInvoiceNumber = '001';
        }

        $invoice = "R-{$year}{$month}{$day}{$newInvoiceNumber}";

        $transaksi = Transaksi::create([
            'id_pengguna' => $id_pengguna,
            'total_harga' => $request->input('total_harga'),
            'tanggal_pelaksanaan' => $tanggal_pelaksanaan,
            'jam_mulai' => $request->input('jam_mulai'),
            'jam_selesai' => $request->input('jam_selesai'),
            'invoice' => $invoice
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
    
        $user = $transaksi->pengguna;
        $totalHarga = $transaksi->total_harga;
    
        if ($user->saldo < $totalHarga) {
            return response()->json([
                'message' => 'Saldo anda kurang.',
            ], 400);
        }
    
        $user->saldo -= $totalHarga;
        $user->save();
    
        $saldo = new Saldo();
        $saldo->id_pengguna = $user->id_pengguna;
        $saldo->total = $totalHarga;
        $saldo->jenis = 'Pembelian';
        $saldo->tanggal = Carbon::today()->format('Y-m-d');
        $saldo->status = 'berhasil';
        $saldo->save();
    
        $transaksi->status_transaksi = 'Sudah Bayar';
        $transaksi->tanggal_pemesanan = Carbon::today()->format('Y-m-d');
        $transaksi->save();
    
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
