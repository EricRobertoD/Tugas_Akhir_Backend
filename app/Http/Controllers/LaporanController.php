<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function top5PenggunaWithMostTransaksi()
    {
        $statuses = [
            'Sudah Bayar', 
            'Dikonfirmasi Penyedia Jasa', 
            'Selesai', 
            'Sedang Menghubungkan'
        ];

        $topPengguna = Pengguna::select('pengguna.nama_pengguna', DB::raw('COUNT(detail_transaksi.id_detail_transaksi) as transaksi_count'))
            ->join('transaksi', 'pengguna.id_pengguna', '=', 'transaksi.id_pengguna')
            ->join('detail_transaksi', 'transaksi.id_transaksi', '=', 'detail_transaksi.id_transaksi')
            ->whereIn('detail_transaksi.status_penyedia_jasa', $statuses)
            ->groupBy('pengguna.nama_pengguna')
            ->orderByDesc('transaksi_count')
            ->limit(5)
            ->get();

        return response()->json($topPengguna);
    }
}
