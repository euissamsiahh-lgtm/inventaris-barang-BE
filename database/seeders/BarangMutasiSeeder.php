<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Barang;
use App\Models\Mutasi;
use App\Models\User;
use Carbon\Carbon;

class BarangMutasiSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing users
        $admin = User::where('email', 'admin@stok.ku')->first();
        $petugas = User::where('email', 'petugas@stok.ku')->first();

        if (!$admin || !$petugas) {
            $this->command->error('User admin@stok.ku atau petugas@stok.ku tidak ditemukan!');
            return;
        }

        $barangs = [
            ['kode_barang' => 'BRG-01', 'nama_barang' => 'Kertas HVS', 'kategori' => 'ATK', 'stok_minimum' => 50, 'satuan' => 'Rim', 'harga_satuan' => 50000],
            ['kode_barang' => 'BRG-02', 'nama_barang' => 'Tinta Printer Hitam', 'kategori' => 'Tinta', 'stok_minimum' => 10, 'satuan' => 'Botol', 'harga_satuan' => 75000],
            ['kode_barang' => 'BRG-03', 'nama_barang' => 'Pulpen', 'kategori' => 'ATK', 'stok_minimum' => 100, 'satuan' => 'Pcs', 'harga_satuan' => 3500],
            ['kode_barang' => 'BRG-04', 'nama_barang' => 'Spidol Papan Tulis', 'kategori' => 'ATK', 'stok_minimum' => 20, 'satuan' => 'Pcs', 'harga_satuan' => 8000],
            ['kode_barang' => 'BRG-05', 'nama_barang' => 'Mouse Wireless', 'kategori' => 'Elektronik', 'stok_minimum' => 5, 'satuan' => 'Pcs', 'harga_satuan' => 150000],
        ];

        $createdBarangs = [];
        foreach ($barangs as $brg) {
            $createdBarangs[] = Barang::create($brg);
        }

        // Generate mutations: Exactly 5 transactions as requested
        // Let's create 2 Masuk and 3 Keluar
        $today = Carbon::now();
        $tujuanList = ['HRD', 'IT', 'Finance', 'Operasional', 'Marketing'];

        // Transaction 1: Masuk
        $barang1 = $createdBarangs[0];
        Mutasi::create([
            'no_referensi' => 'IN/' . $today->copy()->subDays(5)->format('Ymd') . '/001',
            'barang_id' => $barang1->id,
            'user_id' => $admin->id,
            'jenis' => 'masuk',
            'jumlah' => 100,
            'tanggal' => $today->copy()->subDays(5)->format('Y-m-d'),
            'keterangan' => 'Pembelian Awal',
        ]);
        $barang1->increment('stok', 100);

        // Transaction 2: Masuk
        $barang2 = $createdBarangs[1] ?? $createdBarangs[0];
        Mutasi::create([
            'no_referensi' => 'IN/' . $today->copy()->subDays(4)->format('Ymd') . '/002',
            'barang_id' => $barang2->id,
            'user_id' => $admin->id,
            'jenis' => 'masuk',
            'jumlah' => 150,
            'tanggal' => $today->copy()->subDays(4)->format('Y-m-d'),
            'keterangan' => 'Pembelian Tambahan',
        ]);
        $barang2->increment('stok', 150);

        // Transaction 3: Keluar
        Mutasi::create([
            'no_referensi' => 'OUT/' . $today->copy()->subDays(3)->format('Ymd') . '/001',
            'barang_id' => $barang1->id,
            'user_id' => $petugas->id,
            'jenis' => 'keluar',
            'jumlah' => 10,
            'tanggal' => $today->copy()->subDays(3)->format('Y-m-d'),
            'tujuan' => $tujuanList[0], // HRD
            'keterangan' => 'Permintaan Divisi',
        ]);
        $barang1->decrement('stok', 10);

        // Transaction 4: Keluar
        Mutasi::create([
            'no_referensi' => 'OUT/' . $today->copy()->subDays(2)->format('Ymd') . '/002',
            'barang_id' => $barang2->id,
            'user_id' => $petugas->id,
            'jenis' => 'keluar',
            'jumlah' => 5,
            'tanggal' => $today->copy()->subDays(2)->format('Y-m-d'),
            'tujuan' => $tujuanList[1], // IT
            'keterangan' => 'Permintaan Divisi',
        ]);
        $barang2->decrement('stok', 5);

        // Transaction 5: Keluar
        Mutasi::create([
            'no_referensi' => 'OUT/' . $today->copy()->subDays(1)->format('Ymd') . '/003',
            'barang_id' => $barang1->id,
            'user_id' => $petugas->id,
            'jenis' => 'keluar',
            'jumlah' => 20,
            'tanggal' => $today->copy()->subDays(1)->format('Y-m-d'),
            'tujuan' => $tujuanList[2], // Finance
            'keterangan' => 'Permintaan Divisi',
        ]);
        $barang1->decrement('stok', 20);
    }
}
