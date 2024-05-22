<?php
namespace App\Http\Controllers;

use App\Models\Ulasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UlasanController extends Controller
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
        $ulasan = Ulasan::with(['pengguna', 'detailTransaksi.paket.penyediaJasa'])
            ->whereHas('detailTransaksi.paket', function($query) use ($id_penyedia) {
                $query->where('id_penyedia', $id_penyedia);
            })
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Ulasan retrieved successfully',
            'data' => $ulasan,
        ], 200);
    }

    public function store($request)
    {
        $validator = Validator::make($request->all(), [
            'id_detail_transaksi' => 'required',
            'rate_ulasan' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $ulasan = Ulasan::create([
            'id_detail_transaksi' => $request->input('id_detail_transaksi'),
            'rate_ulasan' => $request->input('rate_ulasan'),
            'isi_ulasan' => $request->input('isi_ulasan'),
        ]);

        return response([
            'status' => 'success',
            'message' => 'Ulasan created successfully',
            'data' => $ulasan
        ], 201);
    }
}
