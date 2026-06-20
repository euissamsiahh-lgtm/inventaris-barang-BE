<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Mutasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MutasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Mutasi::with(['barang:id,nama_barang,kode_barang,satuan', 'user:id,name']);

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
                'tanggal' => Carbon::parse($mutasi->tanggal)->format('d/m/y'),
                'no_referensi' => $mutasi->no_referensi,
                'nama_barang' => $mutasi->barang->nama_barang ?? '-',
                'kode_barang' => $mutasi->barang->kode_barang ?? '-',
                'jenis' => ucfirst($mutasi->jenis),
                'jumlah' => $mutasi->jumlah,
                'satuan' => $mutasi->barang->satuan ?? '-',
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
            'tujuan' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'no_referensi' => 'nullable|string|unique:mutasis,no_referensi'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $barang = Barang::findOrFail($request->barang_id);

            // Validasi stok jika keluar
            if ($request->jenis == 'keluar') {
                if ($barang->stok < $request->jumlah) {
                    return response()->json([
                        'message' => 'Gagal menyimpan mutasi',
                        'errors' => ['jumlah' => ['Stok barang tidak mencukupi untuk dikeluarkan.']]
                    ], 422);
                }
                $barang->decrement('stok', $request->jumlah);
            } else {
                $barang->increment('stok', $request->jumlah);
            }

            // Generate No Referensi jika kosong
            $noReferensi = $request->no_referensi;
            if (!$noReferensi) {
                $prefix = $request->jenis == 'masuk' ? 'IN' : 'OUT';
                $dateCode = Carbon::parse($request->tanggal)->format('Ymd');
                $randomNumber = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                $noReferensi = "{$prefix}/{$dateCode}/{$randomNumber}";
            }

            $mutasi = Mutasi::create([
                'no_referensi' => $noReferensi,
                'barang_id' => $request->barang_id,
                'user_id' => auth()->id() ?? 1, // Fallback jika tidak ada auth
                'jenis' => $request->jenis,
                'jumlah' => $request->jumlah,
                'tanggal' => $request->tanggal,
                'tujuan' => $request->tujuan,
                'keterangan' => $request->keterangan
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
        return response()->json(['message' => 'Mutasi tidak dapat diubah setelah disimpan untuk alasan audit.'], 403);
    }

    public function destroy(string $id)
    {
        return response()->json(['message' => 'Mutasi tidak dapat dihapus untuk alasan audit.'], 403);
    }
}
