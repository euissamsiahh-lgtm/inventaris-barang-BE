<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::query();

        // Filter: Cari nama / kode barang
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        // Filter: Kategori
        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori', $request->kategori);
        }

        // Filter: Satuan
        if ($request->has('satuan') && $request->satuan != '') {
            $query->where('satuan', $request->satuan);
        }

        $barangs = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'message' => 'Berhasil mengambil data barang',
            'data' => $barangs
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_barang' => 'required|string|unique:barangs,kode_barang',
            'nama_barang' => 'required|string',
            'kategori' => 'nullable|string',
            'stok' => 'nullable|integer',
            'stok_minimum' => 'nullable|integer',
            'satuan' => 'nullable|string',
            'harga_satuan' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $barang = Barang::create($request->all());

        return response()->json([
            'message' => 'Berhasil menambahkan barang baru',
            'data' => $barang
        ], 201);
    }

    public function show(string $id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail barang',
            'data' => $barang
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_barang' => 'required|string|unique:barangs,kode_barang,' . $id,
            'nama_barang' => 'required|string',
            'kategori' => 'nullable|string',
            'stok' => 'nullable|integer',
            'stok_minimum' => 'nullable|integer',
            'satuan' => 'nullable|string',
            'harga_satuan' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $barang->update($request->all());

        return response()->json([
            'message' => 'Berhasil memperbarui data barang',
            'data' => $barang
        ], 200);
    }

    public function destroy(string $id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        $barang->delete();

        return response()->json([
            'message' => 'Berhasil menghapus barang'
        ], 200);
    }
}
