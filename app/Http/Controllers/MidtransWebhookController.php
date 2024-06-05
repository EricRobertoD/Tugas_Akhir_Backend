<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Saldo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {

        $json = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'Invalid JSON payload'], 400);
        }

        if (!isset($json['order_id'], $json['status_code'], $json['gross_amount'], $json['signature_key'], $json['transaction_status'])) {
            return response()->json(['message' => 'Missing required fields'], 400);
        }

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
            } else {
                Log::error('Saldo not found', ['order_id' => $json['order_id']]);
            }
        }

        return response()->json(['message' => 'Webhook received']);
    }
}
