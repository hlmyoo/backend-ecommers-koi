<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'koi_id',
        'jumlah',
        'harga',
        'subtotal',
        'status_pesanan',
        'status_pembayaran',
        'status_pengiriman',
        'snap_token',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke koi
    public function koi()
    {
        return $this->belongsTo(Koi::class);
    }
}