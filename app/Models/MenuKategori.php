<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuKategori extends Model
{
    use HasFactory;

    protected $table = 'tbl_menu_kategori';
    protected $fillable = ['nama_kategori', 'deskripsi'];

    public function menus()
    {
        return $this->hasMany(Menu::class, 'id_kategori');
    }
}
