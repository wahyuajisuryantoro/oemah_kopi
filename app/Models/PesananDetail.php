<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananDetail extends Model
{
    protected $table = 'tbl_pesanan_detail';

    protected $fillable = [
        'id_pesanan',
        'id_menu',
        'jumlah',
        'harga_satuan',
        'diskon',
        'subtotal',
        'catatan_khusus'
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'diskon' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    // Relasi dengan pesanan
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }

    // Relasi dengan menu
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }

    // Method untuk menghitung subtotal
    public function hitungSubtotal()
    {
        $harga_setelah_diskon = $this->harga_satuan - ($this->harga_satuan * $this->diskon / 100);
        return $harga_setelah_diskon * $this->jumlah;
    }
}