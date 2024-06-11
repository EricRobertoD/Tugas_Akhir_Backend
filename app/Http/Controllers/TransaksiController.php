<?php

namespace App\Http\Controllers;

use App\Models\Saldo;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

    
    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:transaksi,id_transaksi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $ids = $request->input('ids');
        $totalHarga = Transaksi::whereIn('id_transaksi', $ids)->sum('total_harga');

        $user = auth()->user();
        
        if ($user->saldo < $totalHarga) {
            return response()->json([
                'message' => 'Saldo anda kurang.',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $user->saldo -= $totalHarga;
            $user->save();

            $saldo = new Saldo();
            $saldo->id_pengguna = $user->id_pengguna;
            $saldo->total = $totalHarga;
            $saldo->jenis = 'Pembelian';
            $saldo->tanggal = Carbon::today()->format('Y-m-d');
            $saldo->status = 'berhasil';
            $saldo->save();

            $currentDate = Carbon::today();
            $year = $currentDate->year;
            $month = str_pad($currentDate->month, 2, '0', STR_PAD_LEFT);
            $day = str_pad($currentDate->day, 2, '0', STR_PAD_LEFT);

            foreach ($ids as $id) {
                $transaksi = Transaksi::find($id);

                if (!$transaksi) {
                    throw new \Exception('Transaksi not found.');
                }

                $lastTransaksi = Transaksi::whereDate('created_at', $currentDate->toDateString())
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($lastTransaksi) {
                    $lastInvoiceNumber = intval(substr($lastTransaksi->invoice, -3));
                    $newInvoiceNumber = str_pad($lastInvoiceNumber + 1, 3, '0', STR_PAD_LEFT);
                } else {
                    $newInvoiceNumber = '001';
                }

                $invoice = "R-{$year}{$month}{$day}{$newInvoiceNumber}";

                $transaksi->status_transaksi = 'Sudah Bayar';
                $transaksi->tanggal_pemesanan = Carbon::today()->format('Y-m-d');
                $transaksi->invoice = $invoice;
                $transaksi->save();

                $detailTransaksis = $transaksi->detailTransaksi;
                foreach ($detailTransaksis as $detailTransaksi) {
                    $detailTransaksi->status_penyedia_jasa = 'Sedang Menghubungkan';
                    $detailTransaksi->save();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi updated successfully',
                'data' => Transaksi::whereIn('id_transaksi', $ids)->get(),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Update failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
