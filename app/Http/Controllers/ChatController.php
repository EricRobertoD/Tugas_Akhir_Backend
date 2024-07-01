<?php

namespace App\Http\Controllers;

use App\Events\NotifyyFrontend;
use App\Models\Chat;
use App\Models\Pengguna;
use App\Models\PenyediaJasa;
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
            'uid_sender' => $id_pengguna,
        ]);

        broadcast(new NotifyyFrontend($chat, 'channel-' . $id_pengguna))->toOthers();
        broadcast(new NotifyyFrontend($chat, 'channel-' . $chat->id_penyedia))->toOthers();

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
            'uid_sender' => $id_penyedia,
        ]);

        broadcast(new NotifyyFrontend($chat, 'channel-' . $id_penyedia))->toOthers();
        broadcast(new NotifyyFrontend($chat, 'channel-' . $chat->id_pengguna))->toOthers();

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

    public function listPenyediaForPengguna(Request $request)
    {
        $id_pengguna = auth()->user()->id_pengguna;

        $penyediaIds = Chat::where('id_pengguna', $id_pengguna)
            ->select('id_penyedia')
            ->distinct()
            ->get()
            ->pluck('id_penyedia');

        $penyedia = PenyediaJasa::whereIn('id_penyedia', $penyediaIds)->get();

        return response([
            'status' => 'success',
            'message' => 'Penyedia list retrieved successfully',
            'data' => $penyedia
        ], 200);
    }

    public function listPenggunaForPenyedia(Request $request)
    {
        $id_penyedia = auth()->user()->id_penyedia;

        $penyediaIds = Chat::where('id_penyedia', $id_penyedia)
            ->select('id_pengguna')
            ->distinct()
            ->get()
            ->pluck('id_pengguna');

        $pengguna = Pengguna::whereIn('id_pengguna', $penyediaIds)->get();

        return response([
            'status' => 'success',
            'message' => 'Pengguna list retrieved successfully',
            'data' => $pengguna
        ], 200);
    }


    public function storePenggunaFirst(Request $request)
    {
        $id_pengguna = auth()->user()->id_pengguna;
        $validator = Validator::make($request->all(), [
            'id_penyedia' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $chat = Chat::create([
            'id_penyedia' => $request->input('id_penyedia'),
            'id_pengguna' => $id_pengguna,
            'uid_sender' => $id_pengguna,
        ]);

        broadcast(new NotifyyFrontend('Message successfully sent', 'channel-' . $id_pengguna))->toOthers();
        broadcast(new NotifyyFrontend('Message successfully sent', 'channel-' . $chat->id_penyedia))->toOthers();

        return response([
            'status' => 'success',
            'message' => 'Chat created successfully',
            'data' => $chat
        ], 201);
    }

    
    public function storePenyediaFirst(Request $request)
    {
        $id_penyedia = auth()->user()->id_penyedia;
        $validator = Validator::make($request->all(), [
            'id_pengguna' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $chat = Chat::create([
            'id_pengguna' => $request->input('id_pengguna'),
            'id_penyedia' => $id_penyedia,
            'uid_sender' => $id_penyedia,
        ]);

        broadcast(new NotifyyFrontend('Message successfully sent', 'channel-' . $id_penyedia))->toOthers();
        broadcast(new NotifyyFrontend('Message successfully sent', 'channel-' . $chat->id_pengguna))->toOthers();

        return response([
            'status' => 'success',
            'message' => 'Chat created successfully',
            'data' => $chat
        ], 201);
    }
}
