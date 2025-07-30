<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    protected $table = 'tbl_users_detail';

    protected $fillable = [
        'user_id',
        'nama',
        'foto',
        'nomor_telepon',
        'alamat',
        'tanggal_bergabung',
        'aktif',
    ];

    protected $casts = [
        'tanggal_bergabung' => 'date',
        'aktif' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
