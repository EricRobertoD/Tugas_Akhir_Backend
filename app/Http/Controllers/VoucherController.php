<?php

namespace App\Http\Controllers;

use App\Models\TransaksiVoucher;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $voucher = Voucher::get();

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher retrieved successfully',
            'data' => $voucher,
        ], 200);
    }

    public function store(Request $request)
    {
        $id_penyedia = auth()->user()->id_penyedia;
        $validator = Validator::make($request->all(), [
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required',
            'kode_voucher' => 'required',
            'persen' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $voucher = Voucher::create([
            'tanggal_mulai' => $request->input('tanggal_mulai'),
            'tanggal_selesai' => $request->input('tanggal_selesai'),
            'kode_voucher' => $request->input('kode_voucher'),
            'persen' => $request->input('persen'),
            'status' => 'aktif',
        ]);

        return response([
            'status' => 'success',
            'message' => 'Voucher created successfully',
            'data' => $voucher
        ], 201);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $voucher = Voucher::find($id);
    
        if (!$voucher) {
            return response()->json([
                'message' => 'Voucher not found.',
            ], 404);
        }
    
        $validator = Validator::make($request->all(), [
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'persen' => 'required|numeric|min:0|max:100',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
        
        $voucher->tanggal_mulai = $request->input('tanggal_mulai');
        $voucher->tanggal_selesai = $request->input('tanggal_selesai');
        $voucher->persen = $request->input('persen');
        $voucher->save();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Voucher updated successfully',
            'data' => $voucher,
        ], 200);
    }
    

    public function destroy($id)
    {
        $voucher = Voucher::find($id);

        if (!$voucher) {
            return response()->json([
                'message' => 'Voucher not found.',
            ], 404);
        }

        $voucher->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher deleted successfully',
        ], 200);
    }

    public function applyVoucher(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'kode_voucher' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->first('kode_voucher'),
            ], 400);
        }

        $kodeVoucher = $request->input('kode_voucher');

        $voucher = Voucher::where('kode_voucher', $kodeVoucher)
            ->where('status', 'aktif')
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->first();

        if (!$voucher) {
            return response()->json([
                'message' => 'Voucher tidak ketemu atau tidak aktif.',
            ], 404);
        }

        $transaksiVoucher = TransaksiVoucher::where('id_pengguna', $user->id_pengguna)
            ->where('id_voucher', $voucher->id_voucher)
            ->first();

        if ($transaksiVoucher) {
            return response()->json([
                'message' => 'Voucher telah pernah digunakan.',
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher dapat dipakai.',
            'data' => $voucher,
        ], 200);
    }
}
