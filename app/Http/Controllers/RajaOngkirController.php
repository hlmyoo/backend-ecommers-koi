<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RajaOngkirController extends Controller
{
    /**
     * MOCK DATA: Mengembalikan daftar kota/kecamatan tiruan (Public Route)
     * ID menggunakan String Nama Kota agar mudah dicocokkan berdasarkan jarak.
     */
    public function getDaftarKota(Request $request)
    {
        $search = strtolower(trim($request->query('search', '')));

        // Jika ketikan kurang dari 3 huruf, kembalikan array kosong sesuai standar Vue
        if (strlen($search) < 3) {
            return response()->json(['data' => []])
                             ->header('Access-Control-Allow-Origin', '*');
        }

        // Kumpulan data wilayah tiruan dengan ID berupa String murni
        $mockWilayah = [
            [
                'id' => 'lumajang',
                'subdistrict_name' => 'Lumajang',
                'city_name' => 'Kabupaten Lumajang',
                'province_name' => 'Jawa Timur',
                'zip_code' => '67316'
            ],
            [
                'id' => 'mojokerto',
                'subdistrict_name' => 'Mojoanyar',
                'city_name' => 'Kabupaten Mojokerto',
                'province_name' => 'Jawa Timur',
                'zip_code' => '61371'
            ],
            [
                'id' => 'jember',
                'subdistrict_name' => 'Kaliwates',
                'city_name' => 'Kabupaten Jember',
                'province_name' => 'Jawa Timur',
                'zip_code' => '68131'
            ],
            [
                'id' => 'surabaya',
                'subdistrict_name' => 'Gubeng',
                'city_name' => 'Kota Surabaya',
                'province_name' => 'Jawa Timur',
                'zip_code' => '60281'
            ],
            [
                'id' => 'banyuwangi',
                'subdistrict_name' => 'Rogojampi',
                'city_name' => 'Kabupaten Banyuwangi',
                'province_name' => 'Jawa Timur',
                'zip_code' => '68462'
            ]
        ];

        // Filter pencarian berdasarkan inputan user di Vue
        $filtered = [];
        foreach ($mockWilayah as $item) {
            if (str_contains(strtolower($item['subdistrict_name']), $search) || str_contains(strtolower($item['city_name']), $search)) {
                $filtered[] = [
                    'id'    => $item['id'], // Mengirimkan 'lumajang', 'jember', dll. ke database
                    'label' => "Kec. {$item['subdistrict_name']}, {$item['city_name']} - {$item['province_name']} ({$item['zip_code']})"
                ];
            }
        }

        return response()->json(['data' => $filtered])
                         ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * LOGIKA ONGKIR BERDASARKAN JARAK KOTA & BERAT PAKET (Protected Route)
     * Berpusat dari toko e-commerce kamu di JEMBER.
     */
    public function getCost(Request $request)
{
    $kotaAsalToko = 'jember'; 

    // 1. Ambil input dari Vue
    $kotaTujuan = strtolower(trim($request->input('destination', '')));
    
    // 2. AMANKAN BERAT DI SINI:
    // Ambil data berat dari request, konversi ke float
    $beratPaket = (float) $request->input('weight', 1); 

    // 🟢 JIKA FRONTEND VUE MENGIRIM ANGKA 0 ATAU KOSONG, PAKSA JADI 1 KG BIAR TIDAK RP 0
    if ($beratPaket <= 0) {
        $beratPaket = 1.0; 
    }

    // --- Sisa kode pencocokan katalog di bawahnya tetap sama ---
    $tarifPerKg  = 25000;
    $namaLayanan = 'JNE - Tarif Reguler (Luar Wilayah Simulasi)';
    $estimasi    = '2-4 Hari';

    if ($kotaTujuan === $kotaAsalToko) {
        $tarifPerKg  = 7000;
        $namaLayanan = 'JNE - Ongkir Internal Jember (Sangat Dekat)';
        $estimasi    = '1 Hari';
    } else {
        $ongkirKatalog = [
            'lumajang'   => ['tarif' => 15000, 'layanan' => 'JNE - Rute Jember ke Lumajang (Sedang)', 'etd' => '1-2 Hari'],
            'banyuwangi' => ['tarif' => 18000, 'layanan' => 'JNE - Rute Jember ke Banyuwangi (Sedang)', 'etd' => '1-2 Hari'],
            'surabaya'   => ['tarif' => 22000, 'layanan' => 'JNE - Rute Jember ke Surabaya (Sedang)', 'etd' => '1-2 Hari'],
            'mojokerto'  => ['tarif' => 30000, 'layanan' => 'JNE - Rute Jember ke Mojokerto (Jauh)', 'etd' => '2-3 Hari'],
        ];

        if (isset($ongkirKatalog[$kotaTujuan])) {
            $ruteTerpilih = $ongkirKatalog[$kotaTujuan];
            $tarifPerKg  = $ruteTerpilih['tarif'];
            $namaLayanan = $ruteTerpilih['layanan'];
            $estimasi    = $ruteTerpilih['etd'];
        }
    }

    // 3. PROSES PERKALIAN (Sekarang dijamin aman karena berat minimal 1.0)
    $totalOngkir = $tarifPerKg * $beratPaket;

    return response()->json([
        'success' => true,
        'message' => 'Tarif ongkir simulasi Jember berhasil dihitung',
        'data' => [
            [
                'courier'     => 'jne',
                'service'     => 'REG',
                'description' => $namaLayanan,
                'cost'        => $totalOngkir, // Nilai ini akan mengembalikan angka 15000 murni
                'etd'         => $estimasi
            ]
        ]
    ])->header('Access-Control-Allow-Origin', '*');
}
}