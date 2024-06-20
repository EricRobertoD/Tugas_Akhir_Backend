<?php

namespace App\Http\Controllers;

use App\Models\PenyediaJasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PenyediaController extends Controller
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
        $penyedia = PenyediaJasa::where('id_penyedia', $id_penyedia)->with('GambarPorto')->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Penyedia retrieved successfully',
            'data' => $penyedia,
        ], 200);
    }


    public function indexPenyediaSpecific(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id_penyedia' => 'required',
        ]);

        $penyedia = PenyediaJasa::where('id_penyedia', $request->input('id_penyedia'))->with('GambarPorto')->with('Paket')->with(['Paket.DetailTransaksi.Ulasan'])->first();
        return response()->json([
            'status' => 'success',
            'message' => 'Penyedia retrieved successfully',
            'data' => $penyedia,
        ], 200);
    }

    public function updateGambar(Request $request)
    {
        $id_penyedia = auth()->user()->id_penyedia;
        $penyedia = PenyediaJasa::find($id_penyedia);

        if (!$penyedia) {
            return response()->json([
                'message' => 'Penyedia not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'gambar_penyedia' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        if ($request->hasFile('gambar_penyedia')) {
            $filenameWithExt = $request->file('gambar_penyedia')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar_penyedia')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $path = $request->file('gambar_penyedia')->storeAs('gambar', $fileNameToStore, 'public');

            if ($penyedia->gambar_penyedia !== 'noimage.jpg' && !is_null($penyedia->gambar_penyedia)) {
                Storage::disk('public')->delete('gambar/' . $penyedia->gambar_penyedia);
            }

            $penyedia->gambar_penyedia = $fileNameToStore;
        }

        $penyedia->save();

        return response()->json([
            'message' => 'Gambar updated successfully.',
            'data' => $penyedia,
        ], 200);
    }


    public function updateMinimalPersiapan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'minimal_persiapan' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $penyedia = PenyediaJasa::find($user->id_penyedia);

        if (!$penyedia) {
            return response()->json([
                'message' => 'Penyedia not found.',
            ], 404);
        }

        $penyedia->minimal_persiapan = $request->input('minimal_persiapan');
        $penyedia->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Minimal persiapan updated successfully',
            'data' => $penyedia,
        ], 200);
    }
}
