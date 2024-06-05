<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Saldo;
use Carbon\Carbon;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $json = json_decode($request->getContent(), true);

        $signatureKey = hash('sha512', $json['order_id'] . $json['status_code'] . $json['gross_amount'] . env('MIDTRANS_SERVER_KEY'));

        if ($signatureKey !== $json['signature_key']) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        if (in_array($json['transaction_status'], ['capture', 'settlement'])) {
            $saldo = Saldo::where('id', $json['order_id'])->first();

            if ($saldo) {
                $saldo->status = 'berhasil';
                $saldo->save();

                $user = $saldo->id_penyedia ? $saldo->PenyediaJasa : ($saldo->id_pengguna ? $saldo->pengguna : null);
                if ($user) {
                    $user->saldo += $saldo->total;
                    $user->save();
                }
            }
        }

        return response()->json(['message' => 'Webhook received']);
    }
}
