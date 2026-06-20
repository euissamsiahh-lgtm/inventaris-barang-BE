<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'stok',
        'stok_minimum',
        'satuan',
    ];

    public function mutasis()
    {
        return $this->hasMany(Mutasi::class);
    }
}
