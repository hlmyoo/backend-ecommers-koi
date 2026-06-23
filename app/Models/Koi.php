<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Koi extends Model
{
    use HasFactory;

    protected $table = 'koi';

    protected $fillable = [
        'nama_koi',
        'jenis',
        'ukuran',
        'harga',
        'stok',
        'deskripsi',
        'gambar'
    ];
}