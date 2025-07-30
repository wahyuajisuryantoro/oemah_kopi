<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'tbl_menu';
    protected $fillable = [
        'nama_menu',
        'deskripsi',
        'harga_jual',
        'diskon',
        'waktu_masak',
        'tersedia',
        'id_kategori',
        'gambar_url',
        'stok',
        'harga_modal',
    ];

    public function kategori()
    {
        return $this->belongsTo(MenuKategori::class, 'id_kategori');
    }

    public function manajemenMenu()
    {
        return $this->belongsTo(Menu::class);
    }
}
