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

        $this->call(UserSeeder::class);
        $this->call(BarangMutasiSeeder::class);
    }
}
