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
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $petugas = User::where('role', 'petugas')->first() ?? User::first();

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

        // Generate mutations for the last 30 days
        $today = Carbon::now();
        for ($i = 30; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);

            // Randomly pick some items to mutate
            foreach ($createdBarangs as $barang) {
                // 30% chance to have an incoming mutation
                if (rand(1, 100) <= 30) {
                    $masuk = rand(10, 50);
                    Mutasi::create([
                        'barang_id' => $barang->id,
                        'user_id' => $admin->id,
                        'jenis' => 'masuk',
                        'jumlah' => $masuk,
                        'tanggal' => $date->format('Y-m-d'),
                        'keterangan' => 'Restock',
                    ]);
                    $barang->increment('stok', $masuk);
                }

                // 40% chance to have an outgoing mutation, if stock is sufficient
                if (rand(1, 100) <= 40 && $barang->stok > 0) {
                    $keluar = rand(1, min(20, $barang->stok));
                    Mutasi::create([
                        'barang_id' => $barang->id,
                        'user_id' => $petugas->id,
                        'jenis' => 'keluar',
                        'jumlah' => $keluar,
                        'tanggal' => $date->format('Y-m-d'),
                        'keterangan' => 'Pengambilan untuk divisi',
                    ]);
                    $barang->decrement('stok', $keluar);
                }
            }
        }
    }
}
