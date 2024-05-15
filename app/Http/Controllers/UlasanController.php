<?php
namespace App\Http\Controllers;

use App\Models\Ulasan;
use Illuminate\Http\Request;

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
}
