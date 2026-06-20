<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SatuanController extends Controller
{
    public function index(Request $request)
    {
        $query = Satuan::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_satuan', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_satuan', 'like', '%' . $request->search . '%');
        }

        $satuans = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar satuan',
            'data' => $satuans
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_satuan' => 'required|string|max:255',
            'kode_satuan' => 'required|string|max:50',
            'keterangan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $satuan = Satuan::create($request->all());

        return response()->json([
            'message' => 'Berhasil menambahkan satuan',
            'data' => $satuan
        ], 201);
    }

    public function show(string $id)
    {
        $satuan = Satuan::find($id);
        if (!$satuan) {
            return response()->json(['message' => 'Satuan tidak ditemukan'], 404);
        }
        return response()->json(['data' => $satuan], 200);
    }

    public function update(Request $request, string $id)
    {
        $satuan = Satuan::find($id);
        if (!$satuan) {
            return response()->json(['message' => 'Satuan tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_satuan' => 'required|string|max:255',
            'kode_satuan' => 'required|string|max:50',
            'keterangan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $satuan->update($request->all());

        return response()->json([
            'message' => 'Berhasil memperbarui satuan',
            'data' => $satuan
        ], 200);
    }

    public function destroy(string $id)
    {
        $satuan = Satuan::find($id);
        if (!$satuan) {
            return response()->json(['message' => 'Satuan tidak ditemukan'], 404);
        }

        $satuan->delete();

        return response()->json([
            'message' => 'Berhasil menghapus satuan'
        ], 200);
    }
}
