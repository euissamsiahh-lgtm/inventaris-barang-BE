<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Mutasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MutasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Mutasi::with(['barang:id,nama_barang,kode_barang,satuan', 'user:id,name', 'supplier:id,nama_supplier']);

        // Filter: Tanggal (start_date s/d end_date)
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        // Filter: Barang (Nama atau Kode)
        if ($request->has('barang') && $request->barang != '') {
            $search = $request->barang;
            $query->whereHas('barang', function($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        // Filter: Jenis (Masuk / Keluar)
        if ($request->has('jenis') && $request->jenis != '') {
            $query->where('jenis', $request->jenis);
        }

        $mutasis = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        // Kalkulasi Summary
        $totalMasuk = $mutasis->where('jenis', 'masuk')->sum('jumlah');
        $totalKeluar = $mutasis->where('jenis', 'keluar')->sum('jumlah');
        $totalMutasi = $mutasis->count();

        $formattedData = $mutasis->map(function($mutasi) {
            return [
                'id' => $mutasi->id,
                'tanggal' => Carbon::parse($mutasi->tanggal)->format('d/m/Y'),
                'no_referensi' => $mutasi->no_referensi,
                'nama_barang' => $mutasi->barang->nama_barang ?? '-',
                'barang' => $mutasi->barang->nama_barang ?? '-',
                'kode_barang' => $mutasi->barang->kode_barang ?? '-',
                'jenis' => ucfirst($mutasi->jenis),
                'jumlah' => $mutasi->jumlah,
                'satuan' => $mutasi->barang->satuan ?? '-',
                'tujuan' => $mutasi->tujuan ?? '-',
                'supplier' => $mutasi->supplier->nama_supplier ?? '-',
                'keterangan' => $mutasi->keterangan ?? '-',
                'petugas' => $mutasi->user->name ?? '-'
            ];
        });

        return response()->json([
            'message' => 'Berhasil mengambil data mutasi',
            'data' => [
                'summary' => [
                    'total_masuk' => (int) $totalMasuk,
                    'total_keluar' => (int) $totalKeluar,
                    'total_mutasi' => $totalMutasi,
                ],
                'mutasi' => $formattedData
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'barang_id' => 'required|exists:barangs,id',
            'jenis' => 'required|in:masuk,keluar',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Custom validation based on jenis
        if ($request->jenis == 'keluar' && !$request->has('tujuan')) {
             return response()->json([
                'message' => 'Validasi gagal',
                'errors' => ['tujuan' => ['Kolom tujuan wajib diisi untuk barang keluar.']]
            ], 422);
        }

        if ($request->jenis == 'masuk' && !$request->has('supplier_id')) {
             return response()->json([
                'message' => 'Validasi gagal',
                'errors' => ['supplier_id' => ['Kolom supplier wajib diisi untuk barang masuk.']]
            ], 422);
        }

        $barang = Barang::find($request->barang_id);

        if ($request->jenis == 'keluar' && $barang->stok < $request->jumlah) {
            return response()->json([
                'message' => 'Stok barang tidak mencukupi'
            ], 400);
        }

        // Generate nomor referensi
        $prefix = $request->jenis == 'masuk' ? 'IN' : 'OUT';
        $dateStr = Carbon::parse($request->tanggal)->format('Ymd');
        $count = Mutasi::whereDate('tanggal', $request->tanggal)->where('jenis', $request->jenis)->count() + 1;
        $no_referensi = sprintf("%s-%s-%04d", $prefix, $dateStr, $count);

        try {
            DB::beginTransaction();

            if ($request->jenis == 'keluar') {
                $barang->decrement('stok', $request->jumlah);
            } else {
                $barang->increment('stok', $request->jumlah);
            }

            $mutasi = Mutasi::create([
                'no_referensi' => $no_referensi,
                'barang_id' => $request->barang_id,
                'user_id' => Auth::id() ?? 1,
                'jenis' => $request->jenis,
                'jumlah' => $request->jumlah,
                'tanggal' => $request->tanggal,
                'tujuan' => $request->jenis == 'keluar' ? $request->tujuan : null,
                'supplier_id' => $request->jenis == 'masuk' ? $request->supplier_id : null,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil menambahkan mutasi barang',
                'data' => $mutasi
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        $mutasi = Mutasi::with(['barang', 'user'])->find($id);
        if (!$mutasi) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        return response()->json(['data' => $mutasi], 200);
    }

    public function update(Request $request, string $id)
    {
        $mutasi = Mutasi::find($id);
        if (!$mutasi) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'barang_id' => 'required|exists:barangs,id',
            'jenis' => 'required|in:masuk,keluar',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        if ($request->jenis == 'keluar' && !$request->has('tujuan')) {
             return response()->json(['message' => 'Validasi gagal', 'errors' => ['tujuan' => ['Kolom tujuan wajib diisi untuk barang keluar.']]], 422);
        }
        if ($request->jenis == 'masuk' && !$request->has('supplier_id')) {
             return response()->json(['message' => 'Validasi gagal', 'errors' => ['supplier_id' => ['Kolom supplier wajib diisi untuk barang masuk.']]], 422);
        }

        try {
            DB::beginTransaction();

            $oldBarang = Barang::find($mutasi->barang_id);
            // Revert old stock
            if ($mutasi->jenis == 'keluar') {
                $oldBarang->increment('stok', $mutasi->jumlah);
            } else {
                $oldBarang->decrement('stok', $mutasi->jumlah);
            }

            $newBarang = Barang::find($request->barang_id);
            // Check if new stock goes negative
            if ($request->jenis == 'keluar' && $newBarang->stok < $request->jumlah) {
                DB::rollBack();
                return response()->json(['message' => 'Stok barang tidak mencukupi'], 400);
            }

            // Apply new stock
            if ($request->jenis == 'keluar') {
                $newBarang->decrement('stok', $request->jumlah);
            } else {
                $newBarang->increment('stok', $request->jumlah);
            }

            $mutasi->update([
                'barang_id' => $request->barang_id,
                'jenis' => $request->jenis,
                'jumlah' => $request->jumlah,
                'tanggal' => $request->tanggal,
                'tujuan' => $request->jenis == 'keluar' ? $request->tujuan : null,
                'supplier_id' => $request->jenis == 'masuk' ? $request->supplier_id : null,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            return response()->json(['message' => 'Berhasil mengubah mutasi barang', 'data' => $mutasi], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan sistem', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        $mutasi = Mutasi::find($id);
        if (!$mutasi) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        try {
            DB::beginTransaction();

            $barang = Barang::find($mutasi->barang_id);
            // Revert stock
            if ($mutasi->jenis == 'keluar') {
                $barang->increment('stok', $mutasi->jumlah);
            } else {
                $barang->decrement('stok', $mutasi->jumlah);
            }

            $mutasi->delete();

            DB::commit();

            return response()->json(['message' => 'Berhasil menghapus mutasi barang'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan sistem', 'error' => $e->getMessage()], 500);
        }
    }
}
