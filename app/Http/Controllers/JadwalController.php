<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JadwalController extends Controller
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
        $jadwal = Jadwal::where('id_penyedia', $id_penyedia)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal retrieved successfully',
            'data' => $jadwal,
        ], 200);
    }

    public function store(Request $request)
    {
        $id_penyedia = auth()->user()->id_penyedia;

        $existingJadwal = Jadwal::where('id_penyedia', $id_penyedia)
            ->where('hari', $request->input('hari'))
            ->first();

        if ($existingJadwal) {
            return response([
                'message' => 'Jadwal for this provider and day already exists',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'hari' => 'required',
            'jam_buka' => 'required',
            'jam_tutup' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $jadwal = Jadwal::create([
            'id_penyedia' => $id_penyedia,
            'hari' => $request->input('hari'),
            'jam_buka' => $request->input('jam_buka'),
            'jam_tutup' => $request->input('jam_tutup'),
        ]);

        return response([
            'status' => 'success',
            'message' => 'Jadwal created successfully',
            'data' => $jadwal
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::find($id);

        if (!$jadwal) {
            return response()->json([
                'message' => 'Jadwal not found.',
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'hari' => 'required',
            'jam_buka' => 'required',
            'jam_tutup' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
        $jadwal->hari = $request->input('hari');
        $jadwal->jam_buka = $request->input('jam_buka');
        $jadwal->jam_tutup = $request->input('jam_tutup');
        $jadwal->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal updated successfully',
            'data' => $jadwal,
        ], 200);
    }

    public function delete($id)
    {
        $jadwal = Jadwal::find($id);

        if (!$jadwal) {
            return response()->json([
                'message' => 'Jadwal not found.',
            ], 404);
        }

        $jadwal->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal deleted successfully',
        ], 200);
    }
}
