<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Koi;
use Midtrans\Config;   
use Midtrans\Snap; 
use Midtrans\CoreApi;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // ==================== USER BELI KOI ====================
    public function store(Request $request)
    {
        $request->validate([
            'koi_id' => 'required|exists:koi,id',
            'jumlah' => 'required|integer|min:1'
        ]);

        $koi = Koi::findOrFail($request->koi_id);

        if ($koi->stok < $request->jumlah) {
            return response()->json([
                'success' => false,
                'message' => 'Stok koi tidak mencukupi'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id'           => auth()->id(),
                'koi_id'            => $koi->id,
                'jumlah'            => $request->jumlah,
                'harga'             => $koi->harga,
                'subtotal'          => $koi->harga * $request->jumlah,
                'status_pesanan'    => 'pending',
                'status_pembayaran' => 'belum_bayar',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
                'data'    => $order
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ==================== USER LIHAT PESANAN ====================
    // ==================== USER LIHAT PESANAN ====================
    public function myOrders()
    {
        try {
            $orders = Order::with([
                // Mengambil kolom-kolom alamat baru yang sesuai dengan database kamu
                'user' => fn($q) => $q->select('id', 'name', 'email', 'provinsi', 'kota', 'kecamatan', 'alamat_detail', 'kode_pos'),
                'koi'  => fn($q) => $q->select('id', 'nama_koi'),
            ])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

            return response()->json([
                'success' => true,
                'data'    => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }

    // ==================== ADMIN LIHAT SEMUA PESANAN ====================
    public function index()
    {
        try {
            $orders = Order::with([
                'user' => fn($q) => $q->select('id', 'name', 'email'),
                'koi'  => fn($q) => $q->select('id', 'nama_koi'),
            ])
            ->latest()
            ->get();

            return response()->json([
                'success' => true,
                'data'    => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ==================== MIDTRANS ====================
    public function createPayment($id)
{
    try {

        $order = Order::with('user', 'koi')->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $params = [

            'transaction_details' => [
                'order_id' => 'ORDER-' . $order->id . '-' . time(),
                'gross_amount' => (int) $order->subtotal
            ],

            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->user->no_hp
            ],

            'item_details' => [
                [
                    'id' => $order->koi->id,
                    'price' => (int) $order->harga,
                    'quantity' => (int) $order->jumlah,
                    'name' => $order->koi->nama_koi
                ]
            ]

        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        $order->snap_token = $snapToken;
        $order->save();

        return response()->json([
            'success' => true,
            'snap_token' => $snapToken
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

    // ==================== AFTER PAY ====================
    public function afterPay($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status_pembayaran === 'belum_bayar') {
            $order->update(['status_pembayaran' => 'menunggu_konfirmasi']);
        }

        return response()->json(['success' => true]);
    }

    // ==================== ADMIN UPDATE STATUS PESANAN ====================
    public function updateStatusPesanan(Request $request, $id)
    {
        $request->validate([
            'status_pesanan' => 'required|in:pending,disetujui,ditolak'
        ]);

        $order = Order::findOrFail($id);

        if (in_array($order->status_pesanan, ['disetujui', 'ditolak'])) {
            return response()->json([
                'success' => false,
                'message' => 'Status pesanan tidak bisa diubah lagi'
            ], 422);
        }

        $order->update(['status_pesanan' => $request->status_pesanan]);

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diupdate',
            'data'    => $order
        ]);
    }

    // ==================== ADMIN UPDATE STATUS PEMBAYARAN ====================
    public function updateStatusPembayaran(Request $request, $id)
    {
        $request->validate([
            'status_pembayaran' => 'required|in:belum_bayar,menunggu_konfirmasi,lunas,ditolak'
        ]);

        $order = Order::with([
            'koi' => fn($q) => $q->select('id', 'nama_koi', 'stok')
        ])->findOrFail($id);

        if ($order->status_pesanan !== 'disetujui') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan belum disetujui'
            ], 422);
        }

        if (in_array($order->status_pembayaran, ['lunas', 'ditolak'])) {
            return response()->json([
                'success' => false,
                'message' => 'Status pembayaran tidak bisa diubah lagi'
            ], 422);
        }

        $order->update(['status_pembayaran' => $request->status_pembayaran]);

        // Kurangi stok saat lunas
        if ($request->status_pembayaran === 'lunas') {
            $order->koi->decrement('stok', $order->jumlah);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran berhasil diupdate',
            'data'    => $order
        ]);
    }

    public function generateQris($id)
{
    try {

        $order = Order::findOrFail($id);

        $grossAmount = (int) $order->subtotal;

        if ($grossAmount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Total harga tidak valid'
            ], 400);
        }

        // CONFIG MIDTRANS
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $params = [
            'payment_type' => 'qris',

            'transaction_details' => [
                'order_id' => 'ORDER-' . $order->id . '-' . time(),
                'gross_amount' => $grossAmount
            ],

            'qris' => [
                'acquirer' => 'gopay'
            ]
        ];

        $response = \Midtrans\CoreApi::charge($params);

        // 🔥 ambil qr url
        $qrUrl = null;

        if (isset($response->actions)) {
            foreach ($response->actions as $action) {
                if ($action->name === 'generate-qr-code') {
                    $qrUrl = $action->url;
                }
            }
        }

        // update status pembayaran
        $order->update([
            'status_pembayaran' => 'menunggu_konfirmasi'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'QRIS berhasil dibuat',
            'qr_url' => $qrUrl,
            'data' => $response
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => 'Gagal generate QRIS',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function updateStatusPengiriman(Request $request, $id)
{
    // validasi
    $request->validate([
        'status_pengiriman' => 'required|in:menunggu,dikirim,sampai'
    ]);

    // cari order
    $order = Order::find($id);

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Pesanan tidak ditemukan'
        ], 404);
    }

    // pembayaran harus lunas dulu
    if ($order->status_pembayaran !== 'lunas') {
        return response()->json([
            'success' => false,
            'message' => 'Pesanan belum lunas'
        ], 400);
    }

    // update status pengiriman
    $order->status_pengiriman = $request->status_pengiriman;

    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Status pengiriman berhasil diupdate',
        'data' => $order
    ]);
}


}