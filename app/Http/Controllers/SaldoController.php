<?php

namespace App\Http\Controllers;

use App\Models\Saldo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Midtrans\Config;
use Midtrans\Snap;

class SaldoController extends Controller
{
    public function indexPenyedia()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $id_penyedia = $user->id_penyedia;
        $saldo = Saldo::where('id_penyedia', $id_penyedia)->with('PenyediaJasa')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Detail transaksis retrieved successfully',
            'data' => $saldo,
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
        $saldo = Saldo::where('id_pengguna', $id_pengguna)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Detail transaksis retrieved successfully',
            'data' => $saldo,
        ], 200);
    }

    public function deposit(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'total' => 'required|numeric|min:0',
            'gambar_saldo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        if ($request->hasFile('gambar_saldo')) {
            $filenameWithExt = $request->file('gambar_saldo')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar_saldo')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $path = $request->file('gambar_saldo')->storeAs('gambar_saldo', $fileNameToStore, 'public');
        } else {
            $fileNameToStore = 'noimage.jpg';
        }

        $total = $request->input('total');

        $saldo = Saldo::create([
            'id_penyedia' => $user->id_penyedia ?? null,
            'id_pengguna' => $user->id_pengguna ?? null,
            'jenis' => 'deposit',
            'total' => $total,
            'gambar_saldo' => $fileNameToStore,
            'status' => 'pending',
            'tanggal' => Carbon::today()->format('Y-m-d'),
        ]);


        $user = $saldo->id_penyedia ? $saldo->PenyediaJasa : ($saldo->id_pengguna ? $saldo->pengguna : null);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $user->saldo += $saldo->total;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Deposit request submitted successfully',
            'data' => $saldo,
        ], 201);
    }

    public function depositMidtrans(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $total = $request->input('total');

        $adjustedTotal = $total * 0.9;

        $saldo = Saldo::create([
            'id_penyedia' => $user->id_penyedia ?? null,
            'id_pengguna' => $user->id_pengguna ?? null,
            'jenis' => 'deposit',
            'total' => $total,
            'status' => 'pending',
            'tanggal' => Carbon::today()->format('Y-m-d'),
        ]);

        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('services.midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('services.midtrans.is_3ds');

        $telepon = $user->nomor_telepon_pengguna ?? $user->nomor_telepon_penyedia;
        $nama = $user->nama_pengguna ?? $user->nama_penyedia;
        $email = $user->email_pengguna ?? $user->email_penyedia;

        $params = [
            'transaction_details' => [
                'order_id' => $saldo->id_saldo,
                'gross_amount' => $total,
            ],
            'customer_details' => [
                'first_name' => $nama,
                'email' => $email,
                'phone' => $telepon,
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);
        $saldo->snap_token = $snapToken;
        $saldo->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Deposit request submitted successfully',
            'data' => $saldo,
        ], 201);
    }

    
    public function confirmDepositMidtrans($id)
    {
        $saldo = Saldo::find($id);
        if (!$saldo || $saldo->jenis !== 'deposit' || $saldo->status !== 'pending') {
            return response()->json([
                'message' => 'Invalid deposit transaction.',
            ], 400);
        }

        $user = $saldo->id_penyedia ? $saldo->PenyediaJasa : ($saldo->id_pengguna ? $saldo->pengguna : null);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $user->saldo += $saldo->total;
        $user->save();

        $saldo->status = 'berhasil';
        $saldo->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Deposit confirmed successfully',
            'data' => $saldo,
        ], 200);
    }

    public function withdraw(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'total' => 'required|numeric|min:0',
            'nomor_rekening' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $total = $request->input('total');
        if ($user->saldo < $total) {
            return response()->json([
                'message' => 'Saldo tidak cukup.',
            ], 400);
        }

        $saldo = Saldo::create([
            'id_penyedia' => $user->id_penyedia ?? null,
            'id_pengguna' => $user->id_pengguna ?? null,
            'jenis' => 'withdraw',
            'total' => $total,
            'tanggal' => Carbon::today()->format('Y-m-d'),
            'nomor_rekening' => $request->input('nomor_rekening'),
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Withdraw successful',
            'data' => $saldo,
        ], 201);
    }

    public function confirmDeposit($id)
    {
        $saldo = Saldo::find($id);
        if (!$saldo || $saldo->jenis !== 'deposit' || $saldo->status !== 'pending') {
            return response()->json([
                'message' => 'Invalid deposit transaction.',
            ], 400);
        }

        $user = $saldo->id_penyedia ? $saldo->PenyediaJasa : ($saldo->id_pengguna ? $saldo->pengguna : null);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $user->saldo += $saldo->total;
        $user->save();

        $saldo->status = 'berhasil';
        $saldo->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Deposit confirmed successfully',
            'data' => $saldo,
        ], 200);
    }


    public function confirmWithdraw(Request $request, $id)
    {
        $saldo = Saldo::find($id);
        if (!$saldo || $saldo->jenis !== 'withdraw' || $saldo->status !== 'pending') {
            return response()->json([
                'message' => 'Invalid withdraw transaction.',
            ], 400);
        }

        $user = $saldo->id_penyedia ? $saldo->PenyediaJasa : ($saldo->id_pengguna ? $saldo->pengguna : null);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'gambar_saldo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        if ($request->hasFile('gambar_saldo')) {
            $filenameWithExt = $request->file('gambar_saldo')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar_saldo')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $path = $request->file('gambar_saldo')->storeAs('gambar_saldo', $fileNameToStore, 'public');
        } else {
            $fileNameToStore = 'noimage.jpg';
        }


        $user->saldo -= $saldo->total;
        $user->save();
        $saldo->status = 'berhasil';
        $saldo->gambar_saldo = $fileNameToStore;
        $saldo->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Withdraw confirmed successfully',
            'data' => $saldo,
        ], 200);
    }



    public function indexPendingWithdraw()
    {
        $saldo = Saldo::where('status', 'pending')
            ->where('jenis', 'withdraw')
            ->with(['PenyediaJasa', 'Pengguna'])
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Pending withdraws retrieved successfully',
            'data' => $saldo,
        ], 200);
    }

    public function indexPendingDeposit()
    {
        $saldo = Saldo::where('status', 'pending')
            ->where('jenis', 'deposit')
            ->with(['PenyediaJasa', 'Pengguna'])
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Pending deposits retrieved successfully',
            'data' => $saldo,
        ], 200);
    }


    public function rejectDeposit($id)
    {
        $saldo = Saldo::find($id);
        if (!$saldo || $saldo->jenis !== 'deposit' || $saldo->status !== 'pending') {
            return response()->json([
                'message' => 'Invalid deposit transaction.',
            ], 400);
        }

        $saldo->status = 'gagal';
        $saldo->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Deposit rejected successfully',
            'data' => $saldo,
        ], 200);
    }

    public function rejectWithdraw($id)
    {
        $saldo = Saldo::find($id);
        if (!$saldo || $saldo->jenis !== 'withdraw' || $saldo->status !== 'pending') {
            return response()->json([
                'message' => 'Invalid withdraw transaction.',
            ], 400);
        }

        $saldo->status = 'gagal';
        $saldo->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Withdraw rejected successfully',
            'data' => $saldo,
        ], 200);
    }

    public function createPaymentLink(Request $request)
    {
        // Set your Merchant Server Key
        Config::$serverKey = config('services.midtrans.server_key');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        Config::$isProduction = config('services.midtrans.is_production');
        // Set sanitization on (default)
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        // Set 3DS transaction for credit card to true
        Config::$is3ds = config('services.midtrans.is_3ds');

        // Validate request
        $request->validate([
            'amount' => 'required|numeric',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        // Prepare transaction parameters
        $params = [
            'transaction_details' => [
                'order_id' => uniqid(),
                'gross_amount' => $request->amount,
            ],
            'customer_details' => [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
            ],
        ];

        // Create Snap payment link
        $snapToken = Snap::getSnapToken($params);
        $paymentUrl = "https://app.sandbox.midtrans.com/snap/v2/vtweb/" . $snapToken;

        return response()->json(['payment_url' => $paymentUrl]);
    }
}
