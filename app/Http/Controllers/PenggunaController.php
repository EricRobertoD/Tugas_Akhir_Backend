<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PenggunaController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $id_pengguna = $user->id_pengguna;
        $pengguna = Pengguna::where('id_pengguna', $id_pengguna)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'pengguna retrieved successfully',
            'data' => $pengguna,
        ], 200);
    }


    public function updateGambar(Request $request)
    {
        $id_pengguna = auth()->user()->id_pengguna;
        $pengguna = Pengguna::find($id_pengguna);

        if (!$pengguna) {
            return response()->json([
                'message' => 'Pengguna not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'gambar_pengguna' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $fileNameToStore = $pengguna->gambar_pengguna;

        if ($request->hasFile('gambar_pengguna')) {
            $file = $request->file('gambar_pengguna');
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

                if ($pengguna->gambar_pengguna !== 'noimage.jpg' && !is_null($pengguna->gambar_pengguna)) {
                    $objectName = 'gambar/' . $pengguna->gambar_pengguna;
                    $object = $bucket->object($objectName);
                    
                    if ($object->exists()) {
                        $object->delete();
                    }
                }
                $fileContents = file_get_contents($file->getPathname());
                $object = $bucket->upload($fileContents, [
                    'name' => 'gambar/' . $fileNameToStore
                ]);

                $pengguna->gambar_pengguna = $fileNameToStore;
                $pengguna->save();
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
            'data' => $fileNameToStore,
        ], 200);
    }


    public function updatePengguna(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $id_pengguna = $user->id_pengguna;
        $pengguna = Pengguna::find($id_pengguna);

        if (!$pengguna) {
            return response()->json([
                'message' => 'pengguna not found.',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama_pengguna' => 'required',
            'email_pengguna' => 'required|string|email|max:255|unique:penyedia_jasa,email_penyedia|unique:pengguna,email_pengguna,' . $id_pengguna . ',id_pengguna',
            'nomor_telepon_pengguna' => 'required',
            'nomor_whatsapp_pengguna' => 'required',
            'alamat_pengguna' => 'required',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors();
            $response = [
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $errors->toArray()
            ];

            return response()->json($response, 400);
        }

        $pengguna->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'pengguna updated successfully',
            'data' => $pengguna,
        ], 200);
    }
}
