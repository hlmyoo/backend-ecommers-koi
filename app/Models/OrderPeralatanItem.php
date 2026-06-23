<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPeralatanItem extends Model
{
    protected $table = 'order_peralatan_items';

    protected $fillable = [
        'order_peralatan_id',
        'peralatan_id',
        'jumlah',
        'harga',
        'subtotal',
    ];

    public function peralatan()
    {
        return $this->belongsTo(Peralatan::class);
    }

    public function order()
    {
        return $this->belongsTo(OrderPeralatan::class, 'order_peralatan_id');
    }
}