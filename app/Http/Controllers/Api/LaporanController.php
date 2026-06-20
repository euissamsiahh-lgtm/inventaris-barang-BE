<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Mutasi;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function stok(Request $request)
    {
        $query = Barang::query();

        // 1. Terapkan Filter
        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori', $request->kategori);
        }

        if ($request->has('barang_id') && $request->barang_id != '') {
            $query->where('id', $request->barang_id);
        }

        // 2. Ambil Data Barang yang sudah difilter
        $barangs = $query->get();
        $barangIds = $barangs->pluck('id')->toArray();

        // 3. Kalkulasi Summary
        $totalBarang = $barangs->count();
        $totalStok = $barangs->sum('stok');
        
        // Mutasi hanya untuk barang yang terfilter
        $barangMasuk = Mutasi::whereIn('barang_id', $barangIds)->where('jenis', 'masuk')->sum('jumlah');
        $barangKeluar = Mutasi::whereIn('barang_id', $barangIds)->where('jenis', 'keluar')->sum('jumlah');

        // 4. Kalkulasi Tabel Laporan & Status
        $laporan = $barangs->map(function($barang) {
            $hargaStok = $barang->stok * $barang->harga_satuan;
            
            $status = 'Aman';
            if ($barang->stok == 0) {
                $status = 'Habis';
            } elseif ($barang->stok <= $barang->stok_minimum) {
                $status = 'Hampir Habis';
            }

            return [
                'id' => $barang->id,
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->nama_barang,
                'kategori' => $barang->kategori ?? '-',
                'satuan' => $barang->satuan ?? '-',
                'stok_tersedia' => $barang->stok,
                'stok_minimum' => $barang->stok_minimum,
                'harga_satuan' => $barang->harga_satuan,
                'harga_stok' => $hargaStok,
                'status' => $status
            ];
        });

        // 5. Kembalikan Response
        return response()->json([
            'message' => 'Berhasil mengambil data laporan stok',
            'data' => [
                'summary' => [
                    'total_barang' => $totalBarang,
                    'total_stok' => $totalStok,
                    'barang_masuk' => (int) $barangMasuk,
                    'barang_keluar' => (int) $barangKeluar,
                ],
                'laporan' => $laporan
            ]
        ], 200);
    }
}
