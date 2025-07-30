<?php

namespace App\Filament\Pages;

use App\Models\Pesanan;
use App\Models\User;
use Filament\Pages\Page;

class LaporanAnalisis extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Laporan & Analisis';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $slug = 'laporan-analisis';
    protected static string $view = 'filament.pages.laporan-analisis';

    public function getLaporanPenjualan()
    {
        return Pesanan::selectRaw('DATE(waktu_pesan) as tanggal, SUM(total_harga) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function getProdukTerlaris()
    {
        return Pesanan::join('tbl_pesanan_detail', 'tbl_pesanan.id', '=', 'tbl_pesanan_detail.id_pesanan')
            ->join('tbl_menu', 'tbl_pesanan_detail.id_menu', '=', 'tbl_menu.id')
            ->selectRaw('tbl_menu.nama_menu, SUM(tbl_pesanan_detail.jumlah) as total_terjual')
            ->groupBy('tbl_menu.id')
            ->orderBy('total_terjual', 'desc')
            ->take(5)
            ->get();
    }

    public function getPerformaKaryawan()
    {
        return User::join('tbl_pesanan', 'users.id', '=', 'tbl_pesanan.id_kasir')
            ->selectRaw('users.name as nama_karyawan, COUNT(tbl_pesanan.id) as total_pesanan, SUM(tbl_pesanan.total_harga) as total_penjualan')
            ->groupBy('users.id')
            ->orderBy('total_penjualan', 'desc')
            ->get();
    }
}
