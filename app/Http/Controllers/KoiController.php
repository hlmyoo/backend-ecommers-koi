<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Koi;

class KoiController extends Controller
{
    /**
     * GET Semua Data Koi (dengan base64)
     */
    public function index()
    {
        $kois = Koi::all();

        $kois->transform(function ($item) {
            if ($item->gambar) {
                $item->gambar = 'data:image/jpeg;base64,' . base64_encode($item->gambar);
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $kois
        ]);
    }

    /**
     * GET Detail Koi
     */
    public function show($id)
    {
        $koi = Koi::find($id);

        if (!$koi) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        if ($koi->gambar) {
            $koi->gambar = 'data:image/jpeg;base64,' . base64_encode($koi->gambar);
        }

        return response()->json([
            'success' => true,
            'data' => $koi
        ]);
    }

    /**
     * POST Tambah Data
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_koi'  => 'required|string|max:255',
            'jenis'     => 'required|string|max:100',
            'ukuran'    => 'nullable|integer|min:0',
            'harga'     => 'nullable|integer|min:0',
            'stok'      => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'gambar'    => 'nullable|image|max:2048',
        ]);

        $data = $request->only([
            'nama_koi',
            'jenis',
            'ukuran',
            'harga',
            'stok',
            'deskripsi'
        ]);

        // 🔥 simpan binary
        if ($request->hasFile('gambar')) {
            $data['gambar'] = file_get_contents($request->file('gambar'));
        }

        Koi::create($data);

        // ❗ JANGAN kirim binary ke JSON
        return response()->json([
            'success' => true,
            'message' => 'Data Koi berhasil ditambahkan'
        ], 201);
    }

    /**
     * DELETE
     */
    public function destroy($id)
    {
        $koi = Koi::findOrFail($id);
        $koi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Koi berhasil dihapus'
        ]);
    }
    
    /** EDIT
 * UPDATE Data Koi + Gambar
 */
public function update(Request $request, $id)
{
    $koi = Koi::find($id);

    if (!$koi) {
        return response()->json([
            'success' => false,
            'message' => 'Data Koi tidak ditemukan'
        ], 404);
    }

    $request->validate([
        'nama_koi'  => 'required|string|max:255',
        'jenis'     => 'required|string|max:100',
        'ukuran'    => 'nullable|integer|min:0',
        'harga'     => 'nullable|integer|min:0',
        'stok'      => 'required|integer|min:0',
        'deskripsi' => 'nullable|string',
        'gambar'    => 'nullable|image|max:2048',
    ]);

    $data = $request->only([
        'nama_koi',
        'jenis',
        'ukuran',
        'harga',
        'stok',
        'deskripsi'
    ]);

    // 🔥 update gambar jika ada
    if ($request->hasFile('gambar')) {
        $data['gambar'] = file_get_contents($request->file('gambar'));
    }

    $koi->update($data);

    return response()->json([
        'success' => true,
        'message' => 'Data Koi berhasil diupdate'
    ]);
}
}