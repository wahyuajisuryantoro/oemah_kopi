<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'tbl_pesanan';

    protected $fillable = [
        'id_meja',
        'id_kasir',
        'atas_nama',
        'jumlah_pelanggan',
        'waktu_pesan',
        'status',
        'status_pembayaran',
        'total_harga',
        'pajak',
        'catatan',
        'metode_pembayaran',
        'order_id',        
        'snap_token', 
    ];

    // Relasi dengan detail pesanan
    public function details()
    {
        return $this->hasMany(PesananDetail::class, 'id_pesanan');
    }

    // Relasi dengan meja
    public function meja()
    {
        return $this->belongsTo(Meja::class, 'id_meja');
    }

    // Relasi dengan kasir (user)
    public function kasir()
    {
        return $this->belongsTo(User::class, 'id_kasir');
    }

    // Relasi dengan antrian
    public function antrian()
    {
        return $this->hasOne(PesananAntrian::class, 'id_pesanan');
    }

    // Method untuk menghitung total harga
    public function hitungTotalHarga()
    {
        return $this->detail->sum('subtotal');
    }

    // Method untuk menghitung pajak
    public function hitungPajak()
    {
        return $this->total_harga * 0.1; // 10% pajak
    }

    // Method untuk mengupdate status
    public function updateStatus($status)
    {
        $this->status = $status;
        return $this->save();
    }

    // Method untuk mendapatkan label status
    public function getStatusLabelAttribute()
    {
        $labels = [
            'menunggu' => [
                'text' => 'Menunggu',
                'class' => 'warning'
            ],
            'diproses' => [
                'text' => 'Diproses',
                'class' => 'primary'
            ],
            'siap' => [
                'text' => 'Siap',
                'class' => 'info'
            ],
            'diantar' => [
                'text' => 'Diantar',
                'class' => 'success'
            ],
            'selesai' => [
                'text' => 'Selesai',
                'class' => 'secondary'
            ],
            'dibatalkan' => [
                'text' => 'Dibatalkan',
                'class' => 'danger'
            ]
        ];

        return $labels[$this->status] ?? [
            'text' => ucfirst($this->status),
            'class' => 'secondary'
        ];
    }
    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($pesanan) {
            $pesanan->detail()->delete();
            $pesanan->antrian()->delete();
        });
    }
}
