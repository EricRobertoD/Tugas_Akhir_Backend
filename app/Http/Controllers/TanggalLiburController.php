<?php

namespace App\Http\Controllers;

use App\Models\TanggalLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TanggalLiburController extends Controller
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
    $libur = TanggalLibur::where('id_penyedia', $id_penyedia)->get();

    return response()->json([
        'status' => 'success',
        'message' => 'Libur retrieved successfully',
        'data' => $libur,
    ], 200);
}

    public function store(Request $request)
    {
        $id_penyedia = auth()->user()->id_penyedia;
        $validator = Validator::make($request->all(), [
            'tanggal_awal' => 'required',
            'tanggal_akhir' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $libur = TanggalLibur::create([
            'id_penyedia' => $id_penyedia,
            'tanggal_awal' => $request->input('tanggal_awal'),
            'tanggal_akhir' => $request->input('tanggal_akhir'),
        ]);

        return response([
            'status' => 'success',
            'message' => 'Tanggal Libur created successfully',
            'data' => $libur
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $libur = TanggalLibur::find($id);
    
        if (!$libur) {
            return response()->json([
                'message' => 'Tanggal Libur not found.',
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'tanggal_awal' => 'required',
            'tanggal_akhir' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
        $libur->tanggal_awal = $request->input('tanggal_awal');
        $libur->tanggal_akhir = $request->input('tanggal_akhir');
        $libur->save();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Tanggal Libur updated successfully',
            'data' => $libur,
        ], 200);
    }

    public function destroy($id)
    {
        $libur = TanggalLibur::find($id);
    
        if (!$libur) {
            return response()->json([
                'message' => 'Tanggal Libur not found.',
            ], 404);
        }
    
        $libur->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Tanggal Libur deleted successfully',
        ], 200);
    }
}
