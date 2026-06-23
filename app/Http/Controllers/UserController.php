<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * =========================
     * REGISTER
     * =========================
     */
    public function register(Request $request)
{
    // 1. Validasi disesuaikan agar menerima ID angka dari dropdown baru
    $request->validate([
        'name'          => 'required|string|max:255',
        'email'         => 'required|string|email|unique:users',
        'password'      => 'required|string|min:3',
        'no_hp'         => 'required',
        'provinsi'      => 'required|string',
        'kota'          => 'required', // 🟢 String dibuang agar bisa menerima ID angka (integer)
        'kecamatan'     => 'required|string',
        'kode_pos'      => 'required',
        'alamat_detail' => 'required',
    ]);

    // 2. Proses simpan ke database user
    $user = User::create([
        'name'          => $request->name,
        'email'         => $request->email,
        'password'      => bcrypt($request->password),
        'no_hp'         => $request->no_hp,
        'provinsi'      => $request->provinsi,
        'kota'          => $request->kota, // Menyimpan ID numerik (contoh: 231)
        'kecamatan'     => $request->kecamatan,
        'kode_pos'      => $request->kode_pos,
        'alamat_detail' => $request->alamat_detail,
        'role'          => 'user', 
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'User berhasil didaftarkan',
        'data'    => $user,
        'token'   => $token
    ], 201);
}

    /**
     * =========================
     * LOGIN
     * =========================
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        // cek user
        if (!$user || !Hash::check($request->password, $user->password)) {

            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        // hapus token lama
        $user->tokens()->delete();

        // generate sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token'   => $token,
            'data'    => $user
        ]);
    }

    /**
     * =========================
     * PROFILE USER LOGIN
     * =========================
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $request->user()
        ]);
    }

    /**
     * =========================
     * UPDATE PROFILE
     * =========================
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'           => 'required|string|max:255',
            'no_hp'          => 'required|string|max:20',

            // alamat baru
            'provinsi'    => 'required|string',
            'kota'        => 'required|string',
            'kecamatan'      => 'required|string',
            'alamat_detail'  => 'required|string',
            'kode_pos'       => 'required|string',
        ]);

        $user->update([
            'name'           => $request->name,
            'no_hp'          => $request->no_hp,

            // alamat baru
            'provinsi'    => $request->provinsi_id,
            'kota'        => $request->kota_id,
            'kecamatan'      => $request->kecamatan,
            'alamat_detail'  => $request->alamat_detail,
            'kode_pos'       => $request->kode_pos,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diupdate',
            'data'    => $user
        ]);
    }

    /**
     * =========================
     * LOGOUT
     * =========================
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
}