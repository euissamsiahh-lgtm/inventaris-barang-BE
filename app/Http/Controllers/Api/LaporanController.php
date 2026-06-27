<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Mutasi;
use Carbon\Carbon;
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

    public function barangKeluar(Request $request)
    {
        $query = Mutasi::with(['barang:id,nama_barang,kode_barang,satuan,harga_satuan', 'user:id,name'])
            ->where('jenis', 'keluar');

        // Filter: Tanggal (start_date s/d end_date)
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        // Filter: Tujuan
        if ($request->has('tujuan') && $request->tujuan != '') {
            $query->where('tujuan', 'like', '%' . $request->tujuan . '%');
        }

        // Filter: Barang (Nama)
        if ($request->has('barang') && $request->barang != '') {
            $search = $request->barang;
            $query->whereHas('barang', function($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%");
            });
        }

        $mutasis = $query->orderBy('tanggal', 'desc')->get();

        // Summary Calculations
        $totalTransaksi = $mutasis->count();
        $barangKeluar = $mutasis->sum('jumlah');
        $totalNilaiPengeluaran = $mutasis->sum(function($mutasi) {
            return $mutasi->jumlah * ($mutasi->barang->harga_satuan ?? 0);
        });

        // Determine Periode String
        $periode = 'Semua Waktu';
        if ($request->has('start_date') && $request->has('end_date')) {
            $start = \Carbon\Carbon::parse($request->start_date)->translatedFormat('d F Y');
            $end = \Carbon\Carbon::parse($request->end_date)->translatedFormat('d F Y');
            $periode = "{$start} - {$end}";
        }

        // Map Table Data
        $laporan = $mutasis->map(function($mutasi) {
            $hargaSatuan = $mutasi->barang->harga_satuan ?? 0;
            return [
                'id' => $mutasi->id,
                'tanggal' => \Carbon\Carbon::parse($mutasi->tanggal)->format('d/m/y'),
                'no_referensi' => $mutasi->no_referensi,
                'tujuan' => $mutasi->tujuan ?? '-',
                'nama_barang' => $mutasi->barang->nama_barang ?? '-',
                'kode_barang' => $mutasi->barang->kode_barang ?? '-',
                'satuan' => $mutasi->barang->satuan ?? '-',
                'jumlah' => $mutasi->jumlah,
                'harga_satuan' => $hargaSatuan,
                'total' => $mutasi->jumlah * $hargaSatuan,
                'petugas' => $mutasi->user->name ?? '-'
            ];
        });

        return response()->json([
            'message' => 'Berhasil mengambil data laporan barang keluar',
            'data' => [
                'summary' => [
                    'total_transaksi' => $totalTransaksi,
                    'barang_keluar' => (int) $barangKeluar,
                    'total_nilai_pengeluaran' => $totalNilaiPengeluaran,
                    'periode' => $periode
                ],
                'laporan' => $laporan
            ]
        ], 200);
    }

    public function barangMasuk(Request $request)
    {
        $query = Mutasi::with(['barang:id,nama_barang,kode_barang,satuan,harga_satuan', 'supplier:id,nama_supplier'])
                       ->where('jenis', 'masuk');

        // Filter: Tanggal (start_date s/d end_date)
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        // Filter: Supplier
        if ($request->has('supplier') && $request->supplier != '') {
            $searchSupplier = $request->supplier;
            $query->whereHas('supplier', function($q) use ($searchSupplier) {
                $q->where('nama_supplier', 'like', "%{$searchSupplier}%");
            });
        }

        // Filter: Barang
        if ($request->has('barang') && $request->barang != '') {
            $searchBarang = $request->barang;
            $query->whereHas('barang', function($q) use ($searchBarang) {
                $q->where('nama_barang', 'like', "%{$searchBarang}%")
                  ->orWhere('kode_barang', 'like', "%{$searchBarang}%");
            });
        }

        $mutasis = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        $laporan = $mutasis->map(function($mutasi) {
            $harga = $mutasi->barang->harga_satuan ?? 0;
            $total = $harga * $mutasi->jumlah;

            return [
                'id' => $mutasi->id,
                'tanggal_masuk' => Carbon::parse($mutasi->tanggal)->format('d/m/Y'),
                'no_referensi' => $mutasi->no_referensi,
                'supplier' => $mutasi->supplier->nama_supplier ?? '-',
                'barang' => $mutasi->barang->nama_barang ?? '-',
                'satuan' => $mutasi->barang->satuan ?? '-',
                'jumlah' => $mutasi->jumlah,
                'harga_satuan' => $harga,
                'total' => $total,
                'keterangan' => $mutasi->keterangan ?? '-'
            ];
        });

        // Summary Keseluruhan (seperti di Laporan Stok / Dashboard)
        $totalBarang = Barang::count();
        $totalStok = Barang::sum('stok');
        $barangMasuk = Mutasi::where('jenis', 'masuk')->sum('jumlah');
        $barangKeluar = Mutasi::where('jenis', 'keluar')->sum('jumlah');

        return response()->json([
            'message' => 'Berhasil mengambil laporan barang masuk',
            'data' => [
                'summary' => [
                    'total_barang' => $totalBarang,
                    'total_stok' => (int) $totalStok,
                    'barang_masuk' => (int) $barangMasuk,
                    'barang_keluar' => (int) $barangKeluar
                ],
                'laporan' => $laporan
            ]
        ], 200);
    }
}
