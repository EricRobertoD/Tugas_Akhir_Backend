<?php
namespace App\Http\Controllers;

use App\Events\NotifyyFrontend;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function storePengguna(Request $request)
    {
        $id_pengguna = auth()->user()->id_pengguna;
        $validator = Validator::make($request->all(), [
            'isi_chat' => 'required',
            'id_penyedia' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $chat = Chat::create([
            'isi_chat' => $request->input('isi_chat'),
            'id_penyedia' => $request->input('id_penyedia'),
            'id_pengguna' => $id_pengguna,
        ]);

        broadcast(new NotifyyFrontend('Message successfully sent', 'channel-' . $id_pengguna))->toOthers();
        broadcast(new NotifyyFrontend('Message successfully sent', 'channel-' . $chat->id_penyedia))->toOthers();

        return response([
            'status' => 'success',
            'message' => 'Chat created successfully',
            'data' => $chat
        ], 201);
    }

    public function storePenyedia(Request $request)
    {
        $id_penyedia = auth()->user()->id_penyedia;
        $validator = Validator::make($request->all(), [
            'isi_chat' => 'required',
            'id_pengguna' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $chat = Chat::create([
            'isi_chat' => $request->input('isi_chat'),
            'id_penyedia' => $id_penyedia,
            'id_pengguna' => $request->input('id_pengguna'),
        ]);

        broadcast(new NotifyyFrontend('Message successfully sent', 'channel-' . $id_penyedia))->toOthers();
        broadcast(new NotifyyFrontend('Message successfully sent', 'channel-' . $chat->id_pengguna))->toOthers();

        return response([
            'status' => 'success',
            'message' => 'Chat created successfully',
            'data' => $chat
        ], 201);
    }

    public function chatPengguna(Request $request)
    {
        $id_pengguna = auth()->user()->id_pengguna;
        $validator = Validator::make($request->all(), [
            'id_penyedia' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $id_penyedia = $request->input('id_penyedia');

        $chats = Chat::where(function ($query) use ($id_penyedia, $id_pengguna) {
            $query->where('id_penyedia', $id_penyedia)
                  ->where('id_pengguna', $id_pengguna);
        })->orWhere(function ($query) use ($id_penyedia, $id_pengguna) {
            $query->where('id_penyedia', $id_pengguna)
                  ->where('id_pengguna', $id_penyedia);
        })->orderBy('created_at', 'asc')->get();

        return response([
            'status' => 'success',
            'message' => 'Chat messages retrieved successfully',
            'data' => $chats
        ], 200);
    }

    public function chatPenyedia(Request $request)
    {
        $id_penyedia = auth()->user()->id_penyedia;
        $validator = Validator::make($request->all(), [
            'id_pengguna' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $id_pengguna = $request->input('id_pengguna');

        $chats = Chat::where(function ($query) use ($id_penyedia, $id_pengguna) {
            $query->where('id_penyedia', $id_penyedia)
                  ->where('id_pengguna', $id_pengguna);
        })->orWhere(function ($query) use ($id_penyedia, $id_pengguna) {
            $query->where('id_penyedia', $id_pengguna)
                  ->where('id_pengguna', $id_penyedia);
        })->orderBy('created_at', 'asc')->get();

        return response([
            'status' => 'success',
            'message' => 'Chat messages retrieved successfully',
            'data' => $chats
        ], 200);
    }
}
