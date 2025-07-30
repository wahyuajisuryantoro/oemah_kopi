<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PesananAntrian extends Model
{
    protected $table = 'tbl_pesanan_antrian';

    protected $fillable = [
        'id_pesanan',
        'nomor_antrian',
        'status_antrian',
        'waktu_masuk_antrian',
        'waktu_keluar_antrian'
    ];

    protected $casts = [
        'waktu_masuk_antrian' => 'datetime',
        'waktu_keluar_antrian' => 'datetime',
    ];

    // Relasi dengan pesanan
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }

    // Scope untuk antrian hari ini
    public function scopeActive($query)
    {
        return $query->whereIn('status_antrian', ['menunggu', 'diproses']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // Accessors
    public function getDurasiAntrianAttribute()
    {
        if ($this->waktu_keluar_antrian) {
            return $this->waktu_masuk_antrian->diffInMinutes($this->waktu_keluar_antrian);
        }

        return $this->waktu_masuk_antrian->diffInMinutes(now());
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'menunggu' => 'warning',
            'diproses' => 'primary',
            'selesai' => 'success',
            'dibatalkan' => 'danger'
        ];

        return $badges[$this->status_antrian] ?? 'secondary';
    }
}
