<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('id', 'desc')->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar role',
            'data' => $roles
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_role' => 'required|string|max:255|unique:roles',
            'deskripsi' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $role = Role::create($request->all());

        return response()->json([
            'message' => 'Berhasil menambahkan role',
            'data' => $role
        ], 201);
    }

    public function show(string $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Role tidak ditemukan'], 404);
        }
        return response()->json(['data' => $role], 200);
    }

    public function update(Request $request, string $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Role tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_role' => 'required|string|max:255|unique:roles,nama_role,' . $id,
            'deskripsi' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $role->update($request->all());

        return response()->json([
            'message' => 'Berhasil memperbarui role',
            'data' => $role
        ], 200);
    }

    public function destroy(string $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Role tidak ditemukan'], 404);
        }

        $role->delete();

        return response()->json([
            'message' => 'Berhasil menghapus role'
        ], 200);
    }
}
