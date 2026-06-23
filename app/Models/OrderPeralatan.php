<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPeralatan extends Model
{
    protected $table = 'orders_peralatan';

    protected $fillable = [
        'user_id',
        'status_pesanan',
        'status_pembayaran',
        'status_pengiriman',
        'snap_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderPeralatanItem::class, 'order_peralatan_id');
    }
}