<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peralatan;

class PeralatanController extends Controller
{
    // ==================== GET ALL ====================
    public function index()
    {
        try {
            $peralatan = Peralatan::all()->map(function($item) {
                if ($item->gambar && !str_starts_with($item->gambar, 'data:')) {
                    $item->gambar = 'data:image/jpeg;base64,' . $item->gambar;
                }
                return $item;
            });

            return response()->json([
                'success' => true,
                'data' => $peralatan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== STORE ====================
    public function store(Request $request)
    {
        $request->validate([
            'nama_peralatan' => 'required|string',
            'kategori'       => 'required|string',
            'harga'          => 'required|integer|min:0',
            'stok'           => 'required|integer|min:0',
            'deskripsi'      => 'nullable|string',
            'gambar'         => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['nama_peralatan', 'kategori', 'harga', 'stok', 'deskripsi']);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = base64_encode(file_get_contents($request->file('gambar')->getRealPath()));
        }

        $peralatan = Peralatan::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil ditambahkan',
            'data'    => $peralatan
        ], 201);
    }

    // ==================== UPDATE ====================
    public function update(Request $request, $id)
    {
        $peralatan = Peralatan::findOrFail($id);

        $data = $request->only(['nama_peralatan', 'kategori', 'harga', 'stok', 'deskripsi']);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = base64_encode(file_get_contents($request->file('gambar')->getRealPath()));
        }

        $peralatan->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil diupdate',
            'data'    => $peralatan
        ]);
    }

    // ==================== DESTROY ====================
    public function destroy($id)
    {
        $peralatan = Peralatan::findOrFail($id);
        $peralatan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil dihapus'
        ]);
    }
}