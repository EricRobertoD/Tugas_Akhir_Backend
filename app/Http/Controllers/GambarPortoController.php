<?php

namespace App\Http\Controllers;

use App\Models\GambarPorto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GambarPortoController extends Controller
{
    public function index()
    {
        $gambar = GambarPorto::all();

        if (count($gambar) > 0) {
            return response([
                'message' => 'Get all Gambar Success',
                'data' => $gambar
            ], 200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }

    public function store(Request $request)
    {
        $id_penyedia = auth()->user()->id_penyedia;
        $validator = Validator::make($request->all(), [
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $path = $request->file('gambar')->storeAs('gambar', $fileNameToStore, 'public');
        } else {
            $fileNameToStore = 'noimage.jpg';
        }

        $gambar = GambarPorto::create([
            'id_penyedia' => $id_penyedia,
            'gambar' => $fileNameToStore,
        ]);

        return response([
            'status' => 'success',
            'message' => 'Gambar berhasil diunggah',
            'data' => $gambar,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $gambar = GambarPorto::find($id);

        if (!$gambar) {
            return response()->json([
                'message' => 'Gambar not found.',
            ], 404);
        }

        if (auth()->user()->id_penyedia !== $gambar->id_penyedia) {
            return response()->json([
                'message' => 'Unauthorized to edit this gambar.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $path = $request->file('gambar')->storeAs('gambar', $fileNameToStore, 'public');

            if ($gambar->gambar !== 'noimage.jpg') {
                Storage::disk('public')->delete('gambar/' . $gambar->gambar);
            }

            $gambar->gambar = $fileNameToStore;
        }

        $gambar->save();

        return response()->json([
            'message' => 'Gambar updated successfully.',
            'data' => $gambar,
        ], 200);
    }

    public function delete($id)
    {
        $gambar = GambarPorto::find($id);

        if (!$gambar) {
            return response()->json([
                'message' => 'Gambar not found.',
            ], 404);
        }

        // if (auth()->user()->id_penyedia !== $gambar->id_penyedia) {
        //     return response()->json([
        //         'message' => 'Unauthorized to delete this gambar.',
        //     ], 403);
        // }

        if ($gambar->gambar !== 'noimage.jpg') {
            Storage::disk('public')->delete('gambar/' . $gambar->gambar);
        }

        $gambar->delete();

        return response()->json([
            'message' => 'Gambar deleted successfully.',
        ], 200);
    }
}
