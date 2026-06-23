<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderPeralatan;
use App\Models\OrderPeralatanItem;
use App\Models\Peralatan;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class OrderPeralatanController extends Controller
{
    // ==================== USER CHECKOUT CART ====================
    public function store(Request $request)
    {
        $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.peralatan_id' => 'required|exists:peralatan,id',
            'items.*.jumlah'       => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // ==================== BUAT HEADER ORDER ====================
            $order = OrderPeralatan::create([
                'user_id'            => auth()->id(),
                'status_pesanan'     => 'pending',
                'status_pembayaran'  => 'belum_bayar',
                'status_pengiriman'  => 'menunggu',
            ]);

            // ==================== LOOP ITEM CART ====================
            foreach ($request->items as $item) {
                $peralatan = Peralatan::findOrFail($item['peralatan_id']);

                // ==================== VALIDASI STOK ====================
                if ($peralatan->stok < $item['jumlah']) {
                    DB::rollback();

                    return response()->json([
                        'success' => false,
                        'message' => "Stok {$peralatan->nama_peralatan} tidak mencukupi"
                    ], 422);
                }

                // ==================== SIMPAN DETAIL ITEM ====================
                OrderPeralatanItem::create([
                    'order_peralatan_id' => $order->id,
                    'peralatan_id'       => $peralatan->id,
                    'jumlah'             => $item['jumlah'],
                    'harga'              => $peralatan->harga,
                    'subtotal'           => $peralatan->harga * $item['jumlah'],
                ]);
            }

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
                'error'   => $e->getMessage(),
                'line'    => $e->getLine()
            ], 500);
        }
    }

    // ==================== USER LIHAT PESANAN ====================
    public function myOrders()
    {
        try {
            $orders = OrderPeralatan::with([
                // ==================== USER ====================
                'user' => fn($q) => $q->select(
                    'id', 'name', 'email', 'provinsi', 'kota', 'kecamatan', 'alamat_detail', 'kode_pos'
                ),
                // ==================== ITEMS ====================
                'items' => fn($q) => $q->select(
                    'id', 'order_peralatan_id', 'peralatan_id', 'jumlah', 'harga', 'subtotal'
                ),
                // ==================== DATA PERALATAN ====================
                'items.peralatan' => fn($q) => $q->select(
                    'id', 'nama_peralatan', 'gambar'
                ),
            ])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

            // Log data mentah sebelum dikonversi untuk melacak isi subtotal/harga asli
            \Log::info('--- DATA ORDERS USER ---');
            \Log::info($orders->toArray());

            // Paksa casting tipe data relasi item ke float sebelum dilempar ke Vue
            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    $item->subtotal = (float) $item->subtotal;
                    $item->harga = (float) $item->harga;
                }
            }

            // Silakan hilangkan tanda komentar "//" baris di bawah ini jika ingin langsung melihat dump data di browser:
            // dd($orders->toArray());

            return response()->json([
                'success' => true,
                'data'    => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'line'    => $e->getLine()
            ], 500);
        }
    }

    // ==================== ADMIN LIHAT SEMUA PESANAN ====================
    public function index()
    {
        try {
            $orders = OrderPeralatan::with([
                'user' => fn($q) => $q->select(
                    'id', 'name', 'email'
                ),
                'items' => fn($q) => $q->select(
                    'id', 'order_peralatan_id', 'peralatan_id', 'jumlah', 'harga', 'subtotal'
                ),
                'items.peralatan' => fn($q) => $q->select(
                    'id', 'nama_peralatan', 'stok'
                ),
            ])
            ->latest()
            ->get();

            // Log data untuk perbandingan dengan data User
            \Log::info('--- DATA ORDERS ADMIN ---');
            \Log::info($orders->toArray());

            return response()->json([
                'success' => true,
                'data'    => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'line'    => $e->getLine()
            ], 500);
        }
    }

    // ==================== MIDTRANS PAYMENT ====================
    public function createPayment($id)
    {
        try {
            $order = OrderPeralatan::with([
                'user',
                'items.peralatan'
            ])->findOrFail($id);

            if ($order->status_pesanan !== 'disetujui') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan belum disetujui admin'
                ], 400);
            }

            Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
            Config::$isProduction = false;
            Config::$isSanitized  = true;
            Config::$is3ds        = true;

            $grossAmount = $order->items->sum('subtotal');
            $itemDetails = [];

            foreach ($order->items as $item) {
                $itemDetails[] = [
                    'id'       => $item->peralatan->id,
                    'price'    => (int) $item->harga,
                    'quantity' => (int) $item->jumlah,
                    'name'     => substr($item->peralatan->nama_peralatan, 0, 50),
                ];
            }

            $params = [
                'transaction_details' => [
                    'order_id'     => 'PERALATAN-' . $order->id . '-' . time(),
                    'gross_amount' => (int) $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => $order->user->name,
                    'email'      => $order->user->email,
                ],
                'item_details' => $itemDetails
            ];

            $snapToken = Snap::getSnapToken($params);

            $order->update([
                'snap_token' => $snapToken
            ]);

            return response()->json([
                'success'    => true,
                'snap_token' => $snapToken
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pembayaran',
                'error'   => $e->getMessage(),
                'line'    => $e->getLine()
            ], 500);
        }
    }

    // ==================== AFTER PAY ====================
    public function afterPay($id)
    {
        $order = OrderPeralatan::findOrFail($id);

        if ($order->status_pembayaran === 'belum_bayar') {
            $order->update([
                'status_pembayaran' => 'menunggu_konfirmasi'
            ]);
        }

        return response()->json([
            'success' => true
        ]);
    }

    // ==================== ADMIN UPDATE STATUS PESANAN ====================
    public function updateStatusPesanan(Request $request, $id)
    {
        $request->validate([
            'status_pesanan' => 'required|in:pending,disetujui,ditolak'
        ]);

        $order = OrderPeralatan::findOrFail($id);

        if (in_array($order->status_pesanan, ['disetujui', 'ditolak'])) {
            return response()->json([
                'success' => false,
                'message' => 'Status pesanan tidak bisa diubah lagi'
            ], 422);
        }

        $order->update([
            'status_pesanan' => $request->status_pesanan
        ]);

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

        $order = OrderPeralatan::with([
            'items.peralatan' => fn($q) => $q->select(
                'id', 'nama_peralatan', 'stok'
            )
        ])->findOrFail($id);

        // ==================== VALIDASI STATUS PESANAN ====================
        if ($order->status_pesanan !== 'disetujui') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan belum disetujui'
            ], 422);
        }

        // ==================== VALIDASI STATUS PEMBAYARAN ====================
        if (in_array($order->status_pembayaran, ['lunas', 'ditolak'])) {
            return response()->json([
                'success' => false,
                'message' => 'Status pembayaran tidak bisa diubah lagi'
            ], 422);
        }

        // ==================== UPDATE STATUS ====================
        $order->update([
            'status_pembayaran' => $request->status_pembayaran
        ]);

        // ==================== KURANGI STOK SAAT LUNAS ====================
        if ($request->status_pembayaran === 'lunas') {
            foreach ($order->items as $item) {
                if ($item->peralatan) {
                    $item->peralatan->decrement('stok', $item->jumlah);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran berhasil diupdate',
            'data'    => $order
        ]);
    }

    // ==================== UPDATE STATUS PENGIRIMAN ====================
    public function updateStatusPengiriman(Request $request, $id)
    {
        $request->validate([
            'status_pengiriman' => 'required|in:menunggu,dikirim,sampai'
        ]);

        $order = OrderPeralatan::findOrFail($id);

        if ($order->status_pembayaran !== 'lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan belum lunas'
            ], 400);
        }

        $order->update([
            'status_pengiriman' => $request->status_pengiriman
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status pengiriman berhasil diupdate',
            'data'    => $order
        ]);
    }
}