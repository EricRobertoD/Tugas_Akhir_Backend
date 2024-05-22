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
        // $id_pengguna = auth()->user()->id_pengguna;
        // $validator = Validator::make($request->all(), [
        //     'isi_chat' => 'required',
        //     'id_penyedia' => 'required',
        //     'id_pengguna' => 'required',
        // ]);

        // if ($validator->fails()) {
        //     return response([
        //         'message' => 'Validation failed',
        //         'errors' => $validator->errors(),
        //     ], 400);
        // }

        // $chat = Chat::create([
        //     'isi_chat' => $request->input('isi_chat'),
        //     'id_penyedia' => $request->input('id_penyedia'),
        //     'id_pengguna' => $id_pengguna,
        // ]);

        // return response([
        //     'status' => 'success',
        //     'message' => 'Chat created successfully',
        //     'data' => $chat
        // ], 201);

        broadcast(new NotifyyFrontend('hello'))->toOthers();

        return response([
            'status' => 'success',
            'message' => 'Chat created successfully',
            'data' => 'hello world'
        ], 201);
    }
}
