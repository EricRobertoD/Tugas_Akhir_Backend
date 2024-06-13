<?php

namespace App\Http\Controllers;

use App\Models\PenyediaJasa;
use App\Models\Pengguna;
use App\Models\Saldo;
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

    public function countSuccessfulDetailTransaksi(Request $request)
    {
        $roles = [
            'Pembawa Acara',
            'Fotografer',
            'Penyusun Acara',
            'Katering',
            'Dekor',
            'Administrasi',
            'Operasional',
            'Tim Event Organizer'
        ];

        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000'
        ]);

        $month = $request->input('month');
        $year = $request->input('year');

        $successfulTransaksi = PenyediaJasa::select('penyedia_jasa.nama_role', DB::raw('COUNT(detail_transaksi.id_detail_transaksi) as transaksi_count'))
            ->join('paket', 'penyedia_jasa.id_penyedia', '=', 'paket.id_penyedia')
            ->join('detail_transaksi', 'paket.id_paket', '=', 'detail_transaksi.id_paket')
            ->where('detail_transaksi.status_penyedia_jasa', 'Selesai')
            ->whereIn('penyedia_jasa.nama_role', $roles)
            ->whereYear('detail_transaksi.created_at', $year)
            ->whereMonth('detail_transaksi.created_at', $month)
            ->groupBy('penyedia_jasa.nama_role')
            ->get();

        return response()->json($successfulTransaksi);
    }


    public function depositPerMonthPerYear(Request $request)
    {
        $request->validate([
            'start_year' => 'required|integer|min:2000',
            'end_year' => 'required|integer|min:2000|gte:start_year',
        ]);

        $startYear = $request->input('start_year');
        $endYear = $request->input('end_year');

        $depositData = Saldo::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total) as total_deposit')
        )
            ->where('jenis', 'deposit')
            ->where('status', 'berhasil')
            ->whereBetween(DB::raw('YEAR(created_at)'), [$startYear, $endYear])
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at)'), 'asc')
            ->orderBy(DB::raw('MONTH(created_at)'), 'asc')
            ->get();

        $formattedData = $depositData->mapToGroups(function ($item) {
            return [$item->year => [
                'month' => $item->month,
                'total_deposit' => $item->total_deposit
            ]];
        });

        return response()->json($formattedData);
    }
}
