<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenyediaJasa;
use App\Models\Jadwal;
use App\Models\TanggalLibur;
use App\Models\Transaksi;
use App\Models\Paket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class FilterController extends Controller
{
    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_budget' => 'required',
            'end_budget' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'date_time' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $startBudget = $request->input('start_budget');
        $endBudget = $request->input('end_budget');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        $dateTime = $request->input('date_time');

        $date = Carbon::parse($dateTime);
        $dayOfWeek = $date->dayOfWeekIso;

        $penyediaJasa = PenyediaJasa::query()
            ->whereHas('Jadwal', function ($query) use ($dayOfWeek, $startTime, $endTime) {
                $query->where('hari', $dayOfWeek)
                      ->where('jam_buka', '<=', $startTime)
                      ->where('jam_tutup', '>=', $endTime);
            })
            ->whereDoesntHave('TanggalLibur', function ($query) use ($date) {
                $query->where('tanggal_awal', '<=', $date)
                      ->where('tanggal_akhir', '>=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereHas('Paket.DetailTransaksi.Transaksi', function ($query) use ($date) {
                    $query->where('tanggal_pelaksanaan', '=', $date)
                          ->where('status_transaksi', 'Sudah Bayar');
                }, '<=', 0)
                ->orDoesntHave('Paket.DetailTransaksi.Transaksi');
            })
            ->whereHas('Paket', function ($query) use ($startBudget, $endBudget) {
                $query->where('harga_paket', '>=', $startBudget)
                      ->where('harga_paket', '<=', $endBudget);
            })
            ->get();

        return response()->json($penyediaJasa);
    }
}
