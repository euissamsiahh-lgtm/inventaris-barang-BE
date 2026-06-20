<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mutasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_referensi',
        'barang_id',
        'user_id',
        'jenis',
        'jumlah',
        'tanggal',
        'tujuan',
        'keterangan',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
