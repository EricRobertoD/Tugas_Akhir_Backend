<?php

namespace App\Http\Controllers;

use App\Models\PenyediaJasa;
use Google\Cloud\Storage\StorageClient;
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

        $fileNameToStore = $penyedia->gambar_penyedia;

        if ($request->hasFile('gambar_penyedia')) {
            $file = $request->file('gambar_penyedia');
            $filenameWithExt = $file->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            try {
                $privateKey = str_replace("\\n", "\n", getenv('private_key'));
                $storage = new StorageClient([
                    'projectId' => getenv('PROJECT_ID'),
                    'keyFile' => [
                        'type' => getenv('type'),
                        'project_id' => getenv('project_id'),
                        'private_key_id' => getenv('private_key_id'),
                        'private_key' => $privateKey,
                        'client_email' => getenv('client_email'),
                        'client_id' => getenv('client_id'),
                        'auth_uri' => getenv('auth_uri'),
                        'token_uri' => getenv('token_uri'),
                        'auth_provider_x509_cert_url' => getenv('auth_provider_x509_cert_url'),
                        'client_x509_cert_url' => getenv('client_x509_cert_url'),
                        'universe_domain' => getenv('universe_domain'),
                    ],
                ]);

                $bucket = $storage->bucket('tugasakhir_11007');

                if ($penyedia->gambar_penyedia !== 'noimage.jpg' && !is_null($penyedia->gambar_penyedia)) {
                    $objectName = 'gambar/' . $penyedia->gambar_penyedia;
                    $bucket->object($objectName)->delete();
                }

                $fileContents = file_get_contents($file->getPathname());
                $object = $bucket->upload($fileContents, [
                    'name' => 'gambar/' . $fileNameToStore
                ]);
                $penyedia->gambar_penyedia = $fileNameToStore;
                $penyedia->save();
            } catch (\Exception $e) {
                return response([
                    'status' => 'error',
                    'message' => 'File upload failed',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        return response([
            'status' => 'success',
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
