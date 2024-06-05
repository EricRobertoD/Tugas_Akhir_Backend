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
        // Log the raw request content for debugging
        Log::info('Raw webhook request', ['content' => $request->getContent()]);

        // Parse the URL-encoded payload
        parse_str($request->getContent(), $json);

        // Log the parsed payload for debugging
        Log::info('Parsed webhook request', ['json' => $json]);

        // Check if parsing was successful
        if (is_null($json)) {
            Log::error('Failed to parse URL-encoded payload', ['content' => $request->getContent()]);
            return response()->json(['message' => 'Invalid URL-encoded payload'], 400);
        }

        // Ensure the required fields are present
        if (!isset($json['order_id'], $json['status_code'], $json['gross_amount'], $json['signature_key'])) {
            Log::error('Missing required fields in parsed payload', ['json' => $json]);
            return response()->json(['message' => 'Missing required fields'], 400);
        }

        // Calculate the signature key
        $signatureKey = hash('sha512', $json['order_id'] . $json['status_code'] . $json['gross_amount'] . env('MIDTRANS_SERVER_KEY'));

        // Log received data for debugging
        Log::info('Webhook received', $json);

        // Validate the signature key
        if ($signatureKey !== $json['signature_key']) {
            Log::error('Invalid signature', ['received' => $json['signature_key'], 'calculated' => $signatureKey]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Check transaction status
        if (in_array($json['transaction_status'], ['capture', 'settlement'])) {
            // Find the deposit record by order_id
            $saldo = Saldo::where('id', $json['order_id'])->first();
            Log::info('Transaction status updated', ['order_id' => $json['order_id'], 'status' => $json['transaction_status']]);

            if ($saldo) {
                $saldo->status = 'berhasil';
                $saldo->save();

                // Update user's balance
                $user = $saldo->id_penyedia ? $saldo->PenyediaJasa : ($saldo->id_pengguna ? $saldo->pengguna : null);
                if ($user) {
                    $user->saldo += $saldo->total;
                    $user->save();
                    Log::info('User balance updated', ['user_id' => $user->id, 'new_balance' => $user->saldo]);
                }
            } else {
                Log::error('Saldo record not found', ['order_id' => $json['order_id']]);
            }
        }

        return response()->json(['message' => 'Webhook received']);
    }
}
