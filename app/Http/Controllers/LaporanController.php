<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function top5PenggunaWithMostTransaksi(Request $request)
    {
        $statuses = [
            'Sudah Bayar',
            'Dikonfirmasi Penyedia Jasa',
            'Selesai',
            'Sedang Menghubungkan'
        ];

        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000'
        ]);

        $month = $request->input('month');
        $year = $request->input('year');

        $topPengguna = Pengguna::select('pengguna.nama_pengguna', DB::raw('COUNT(detail_transaksi.id_detail_transaksi) as transaksi_count'))
            ->join('transaksi', 'pengguna.id_pengguna', '=', 'transaksi.id_pengguna')
            ->join('detail_transaksi', 'transaksi.id_transaksi', '=', 'detail_transaksi.id_transaksi')
            ->whereIn('detail_transaksi.status_penyedia_jasa', $statuses)
            ->whereYear('detail_transaksi.created_at', $year)
            ->whereMonth('detail_transaksi.created_at', $month)
            ->groupBy('pengguna.nama_pengguna')
            ->orderByDesc('transaksi_count')
            ->limit(5)
            ->get();

        return response()->json($topPengguna);
    }
}
