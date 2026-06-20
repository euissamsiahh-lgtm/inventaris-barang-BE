<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $query = Kategori::query();

        // Search feature
        if ($request->has('search') && $request->search != '') {
            $query->where('nama_kategori', 'like', '%' . $request->search . '%');
        }

        $kategoris = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar kategori',
            'data' => $kategoris
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $kategori = Kategori::create($request->all());

        return response()->json([
            'message' => 'Berhasil menambahkan kategori',
            'data' => $kategori
        ], 201);
    }

    public function show(string $id)
    {
        $kategori = Kategori::find($id);
        if (!$kategori) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }
        return response()->json(['data' => $kategori], 200);
    }

    public function update(Request $request, string $id)
    {
        $kategori = Kategori::find($id);
        if (!$kategori) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $kategori->update($request->all());

        return response()->json([
            'message' => 'Berhasil memperbarui kategori',
            'data' => $kategori
        ], 200);
    }

    public function destroy(string $id)
    {
        $kategori = Kategori::find($id);
        if (!$kategori) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        $kategori->delete();

        return response()->json([
            'message' => 'Berhasil menghapus kategori'
        ], 200);
    }
}
