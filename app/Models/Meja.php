<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    use HasFactory;

    protected $table = 'tbl_meja';

    protected $fillable = [
        'nomor_meja',
        'kapasitas',
        'tersedia',
        'lokasi',
        'qr_code_token',
        'qr_code_path'
    ];

    protected $casts = [
        'tersedia' => 'boolean',
    ];

    public function pesanan_aktif()
    {
        return $this->hasMany(Pesanan::class, 'id_meja')
                    ->whereIn('status', ['menunggu', 'diproses']);
    }

    // Relasi dengan semua pesanan
    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_meja');
    }

    // Accessor untuk mendapatkan kapasitas tersedia
    public function getKapasitasTersediaAttribute()
    {
        $kapasitas_terpakai = $this->pesanan_aktif->sum('jumlah_pelanggan');
        return $this->kapasitas - $kapasitas_terpakai;
    }

    // Accessor untuk URL QR code
    public function getQrCodeUrlAttribute()
    {
        if ($this->qr_code_path) {
            return asset($this->qr_code_path);
        }
        return null;
    }

    // Accessor untuk URL pemesanan
    public function getOrderUrlAttribute()
    {
        if ($this->qr_code_token) {
            return url('/pemesanan/meja/' . $this->id . '?token=' . $this->qr_code_token);
        }
        return null;
    }
}