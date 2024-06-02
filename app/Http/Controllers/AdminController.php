<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $id_admin = $user->id_admin;
        $admin = Admin::where('id_admin', $id_admin)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Admin retrieved successfully',
            'data' => $admin,
        ], 200);
    }
}
