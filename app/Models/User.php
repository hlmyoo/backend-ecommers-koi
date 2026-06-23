<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

// 🔥 INI PENTING
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens; // 🔥 HARUS ADA

    protected $fillable = [
        'name',
        'email',
        'password',
        'alamat_detail',
        'kecamatan',
        'kota',
        'provinsi',
        'kode_pos',
        'no_hp',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}