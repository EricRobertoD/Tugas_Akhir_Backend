<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\Saldo;
use App\Models\Transaksi;
use Carbon\Carbon;
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
        })
            ->whereNotNull('status_penyedia_jasa')
            ->with('Paket.PenyediaJasa', 'Transaksi.Pengguna')
            ->get();

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
        })
            ->whereNotNull('status_penyedia_jasa')
            ->with('Paket.PenyediaJasa', 'Transaksi.Pengguna')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Detail transaksis retrieved successfully',
            'data' => $detailTransaksis,
        ], 200);
    }

    public function getFaktur($id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        $transaksi = Transaksi::with('DetailTransaksi.Paket.PenyediaJasa')->with('Pengguna')
            ->whereHas('DetailTransaksi', function ($query) use ($id) {
                $query->where('invoice', $id);
            })
            ->first();

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi not found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Faktur retrieved successfully',
            'data' => $transaksi,
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
            'tanggal_pelaksanaan' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'essage' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $tanggal_pelaksanaan = $request->input('tanggal_pelaksanaan');
        $jam_mulai = Carbon::parse($request->input('jam_mulai'));
        $jam_selesai = Carbon::parse($request->input('jam_selesai'));

        if ($jam_selesai->lessThanOrEqualTo($jam_mulai)) {
            return response([
                'message' => 'Validation failed',
                'errors' => [
                    'jam_selesai' => ['The jam selesai must be after jam mulai.']
                ],
            ], 400);
        }

        $total_minutes = $jam_mulai->diffInMinutes($jam_selesai);
        $total_hours = ceil($total_minutes / 60);

        $subtotal_per_hour = $request->input('subtotal');
        $subtotal = $subtotal_per_hour * $total_hours;

        $transaksi = Transaksi::where('id_pengguna', $id_pengguna)
            ->whereNull('status_transaksi')
            ->whereHas('detailTransaksi', function ($query) use ($tanggal_pelaksanaan) {
                $query->where('tanggal_pelaksanaan', $tanggal_pelaksanaan);
            })
            ->first();

        if (!$transaksi) {
            $transaksi = Transaksi::create([
                'id_pengguna' => $id_pengguna,
                'status_transaksi' => null,
                'total_harga' => 0,
                'tanggal_pelaksanaan' => $tanggal_pelaksanaan,
            ]);
        }

        $detailTransaksi = DetailTransaksi::create([
            'id_pengguna' => $id_pengguna,
            'id_transaksi' => $transaksi->id_transaksi,
            'id_paket' => $request->input('id_paket'),
            'subtotal' => $subtotal,
            'tanggal_pelaksanaan' => $request->input('tanggal_pelaksanaan'),
            'jam_mulai' => $request->input('jam_mulai'),
            'jam_selesai' => $request->input('jam_selesai'),
        ]);

        $transaksi->total_harga += $subtotal;
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

    public function deleteKeranjang(Request $request, $id_detail_transaksi)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $id_pengguna = $user->id_pengguna;

        $detailTransaksi = DetailTransaksi::where('id_detail_transaksi', $id_detail_transaksi)
            ->whereHas('Transaksi', function ($query) use ($id_pengguna) {
                $query->where('id_pengguna', $id_pengguna)
                    ->whereNull('status_transaksi');
            })->first();

        if (!$detailTransaksi) {
            return response()->json([
                'status' => 'error',
                'message' => 'DetailTransaksi not found or not eligible for deletion',
            ], 404);
        }

        $transaksi = $detailTransaksi->Transaksi;

        $detailTransaksi->delete();

        $transaksi->total_harga -= $detailTransaksi->subtotal;
        $transaksi->save();

        return response()->json([
            'status' => 'success',
            'message' => 'DetailTransaksi deleted successfully',
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

    public function updateStatusBerlangsung(Request $request, $id)
    {
        $transaksi = DetailTransaksi::find($id);

        if (!$transaksi) {
            return response()->json([
                'message' => 'Detail Transaksi not found.',
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'status_berlangsung' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
        $transaksi->status_berlangsung = $request->input('status_berlangsung');
        $transaksi->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi updated successfully',
            'data' => $transaksi,
        ], 200);
    }

    public function confirmDetailTransaksi(Request $request, $id)
    {
        $detailTransaksi = DetailTransaksi::find($id);

        if (!$detailTransaksi) {
            return response()->json([
                'message' => 'DetailTransaksi not found.',
            ], 404);
        }

        $penyediaJasa = $detailTransaksi->paket->penyediaJasa;
        $subtotal = $detailTransaksi->subtotal;

        $penyediaJasa->saldo += $subtotal;
        $penyediaJasa->save();

        $penyediaSaldo = new Saldo();
        $penyediaSaldo->id_penyedia = $penyediaJasa->id_penyedia;
        $penyediaSaldo->total = $subtotal;
        $penyediaSaldo->jenis = 'Penjualan';
        $penyediaSaldo->tanggal = Carbon::today()->format('Y-m-d');
        $penyediaSaldo->status = 'berhasil';
        $penyediaSaldo->save();

        $detailTransaksi->status_penyedia_jasa = 'Sedang bekerja sama dengan pelanggan';
        $detailTransaksi->save();

        return response()->json([
            'status' => 'success',
            'message' => 'DetailTransaksi confirmed successfully',
            'data' => $detailTransaksi,
        ], 200);
    }

    public function cancelDetailTransaksi(Request $request, $id)
    {
        $detailTransaksi = DetailTransaksi::find($id);

        if (!$detailTransaksi) {
            return response()->json([
                'message' => 'DetailTransaksi not found.',
            ], 404);
        }

        $transaksi = $detailTransaksi->transaksi;
        $pengguna = $transaksi->pengguna;
        $subtotal = $detailTransaksi->subtotal;

        $pengguna->saldo += $subtotal;
        $pengguna->save();

        $penggunaSaldo = new Saldo();
        $penggunaSaldo->id_pengguna = $pengguna->id_pengguna;
        $penggunaSaldo->total = $subtotal;
        $penggunaSaldo->jenis = 'Refund';
        $penggunaSaldo->tanggal = Carbon::today()->format('Y-m-d');
        $penggunaSaldo->status = 'berhasil';
        $penggunaSaldo->save();

        $detailTransaksi->status_penyedia_jasa = 'Transaksi dibatalkan';
        $detailTransaksi->save();

        return response()->json([
            'status' => 'success',
            'message' => 'DetailTransaksi canceled successfully',
            'data' => $detailTransaksi,
        ], 200);
    }
}
