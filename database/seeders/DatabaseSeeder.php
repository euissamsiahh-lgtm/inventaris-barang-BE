<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Kategori Seeder
        \App\Models\Kategori::create(['nama_kategori' => 'Alat Tulis Kantor', 'jumlah' => 45]);
        \App\Models\Kategori::create(['nama_kategori' => 'Elektronik', 'jumlah' => 20]);
        \App\Models\Kategori::create(['nama_kategori' => 'Perabotan', 'jumlah' => 15]);

        // Satuan Seeder
        \App\Models\Satuan::create(['nama_satuan' => 'Pcs', 'kode_satuan' => 'PCS', 'keterangan' => 'Pieces']);
        \App\Models\Satuan::create(['nama_satuan' => 'Dus', 'kode_satuan' => 'DUS', 'keterangan' => 'Dus/Kotak']);
        \App\Models\Satuan::create(['nama_satuan' => 'Rim', 'kode_satuan' => 'RIM', 'keterangan' => 'Rim (500 Lembar)']);

        // Supplier Seeder
        \App\Models\Supplier::create(['nama_supplier' => 'CV. Sukses Jaya', 'alamat' => 'Jl. Merdeka No. 0, Jakarta', 'no_hp' => '081234567890', 'email' => 'suksesjaya@gmail.com']);
        \App\Models\Supplier::create(['nama_supplier' => 'PT. Makmur Sentosa', 'alamat' => 'Jl. Sudirman No. 10, Bandung', 'no_hp' => '089876543210', 'email' => 'makmursentosa@yahoo.com']);

        $this->call(UserSeeder::class);
        $this->call(BarangMutasiSeeder::class);
    }
}
