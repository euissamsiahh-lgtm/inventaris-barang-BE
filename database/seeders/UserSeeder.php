<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat Roles
        $roleAdmin = \App\Models\Role::create([
            'nama_role' => 'Administrator',
            'deskripsi' => 'Memiliki akses penuh ke semua fitur.'
        ]);

        $rolePetugas = \App\Models\Role::create([
            'nama_role' => 'Petugas Gudang',
            'deskripsi' => 'Akses terbatas untuk mengelola stok barang.'
        ]);

        // Membuat Users
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@stok.ku',
            'password' => Hash::make('password123'),
            'role_id' => $roleAdmin->id,
            'status' => true
        ]);

        User::create([
            'name' => 'Petugas Gudang',
            'email' => 'petugas@stok.ku',
            'password' => Hash::make('password123'),
            'role_id' => $rolePetugas->id,
            'status' => false // Sesuai dengan desain (Tidak Aktif)
        ]);
    }
}
