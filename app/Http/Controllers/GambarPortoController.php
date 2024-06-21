<?php

namespace App\Http\Controllers;

use App\Models\GambarPorto;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $validator = Validator::make($request->all(), [
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);
    
        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        $fileNameToStore = 'noimage.jpg';
    
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
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
    
                $fileContents = file_get_contents($file->getPathname());
    
                $object = $bucket->upload($fileContents, [
                    'name' => 'gambar/' . $fileNameToStore
                ]);
    
                $gambar = GambarPorto::create([
                    'id_penyedia' => auth()->user()->id_penyedia,
                    'gambar' => $fileNameToStore,
                ]);
    
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
            'message' => 'Gambar berhasil diunggah',
            'data' => $fileNameToStore,
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
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);
    
        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
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
                $fileContents = file_get_contents($file->getPathname());
                $object = $bucket->upload($fileContents, [
                    'name' => 'gambar/' . $fileNameToStore
                ]);
                $gambar->gambar = $fileNameToStore;
            } catch (\Exception $e) {
                return response([
                    'status' => 'error',
                    'message' => 'File upload failed',
                    'error' => $e->getMessage(),
                ], 500);
            }
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
