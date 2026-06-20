<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Mutasi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thirtyDaysAgo = Carbon::today()->subDays(30);

        // 1. Summary Cards
        $totalBarang = Barang::count();
        $totalStok = Barang::sum('stok');
        
        $barangMasukBulanIni = Mutasi::where('jenis', 'masuk')
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year)
            ->sum('jumlah');
            
        $barangKeluarBulanIni = Mutasi::where('jenis', 'keluar')
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year)
            ->sum('jumlah');

        $barangStokMinimum = Barang::whereColumn('stok', '<=', 'stok_minimum')->count();

        // 2. Mutasi Cards (Masuk, Keluar, Total)
        $totalMutasiMasuk = Mutasi::where('jenis', 'masuk')->count();
        $totalMutasiKeluar = Mutasi::where('jenis', 'keluar')->count();
        $totalMutasi = $totalMutasiMasuk + $totalMutasiKeluar;

        // 3. Stok Menipis Table
        $stokMenipis = Barang::whereColumn('stok', '<=', 'stok_minimum')
            ->select('id', 'nama_barang', 'stok', 'stok_minimum', 'satuan')
            ->orderBy('stok', 'asc')
            ->take(10)
            ->get();

        // 4. Aktivitas Terbaru Table
        $aktivitasTerbaru = Mutasi::with(['barang:id,nama_barang', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($mutasi) {
                return [
                    'id' => $mutasi->id,
                    'tanggal' => Carbon::parse($mutasi->tanggal)->format('d/m/y'),
                    'jenis' => ucfirst($mutasi->jenis),
                    'nama_barang' => $mutasi->barang->nama_barang ?? 'Unknown',
                    'jumlah' => $mutasi->jumlah,
                    'oleh' => $mutasi->user->name ?? 'Unknown'
                ];
            });

        // 5. Grafik 30 Hari (Daily Data)
        $grafikMasuk = Mutasi::where('jenis', 'masuk')
            ->where('tanggal', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(tanggal) as date, SUM(jumlah) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $grafikKeluar = Mutasi::where('jenis', 'keluar')
            ->where('tanggal', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(tanggal) as date, SUM(jumlah) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dates = [];
        $dataMasuk = [];
        $dataKeluar = [];
        
        for ($i = 30; $i >= 0; $i--) {
            $dateStr = Carbon::today()->subDays($i)->format('Y-m-d');
            $dates[] = Carbon::today()->subDays($i)->format('d M');
            $dataMasuk[] = $grafikMasuk->get($dateStr, 0);
            $dataKeluar[] = $grafikKeluar->get($dateStr, 0);
        }

        return response()->json([
            'message' => 'Berhasil mengambil data dashboard',
            'data' => [
                'summary' => [
                    'total_jenis_barang' => $totalBarang,
                    'total_stok_keseluruhan' => $totalStok,
                    'barang_masuk_bulan_ini' => (int) $barangMasukBulanIni,
                    'barang_keluar_bulan_ini' => (int) $barangKeluarBulanIni,
                    'jumlah_barang_stok_minimum' => $barangStokMinimum,
                ],
                'mutasi' => [
                    'masuk' => $totalMutasiMasuk,
                    'keluar' => $totalMutasiKeluar,
                    'total_mutasi' => $totalMutasi,
                ],
                'stok_menipis' => $stokMenipis,
                'aktivitas_terbaru' => $aktivitasTerbaru,
                'grafik_30_hari' => [
                    'labels' => $dates,
                    'data_masuk' => $dataMasuk,
                    'data_keluar' => $dataKeluar
                ]
            ]
        ], 200);
    }
}
