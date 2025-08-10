<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Pesanan;
use Carbon\Carbon;

class StatsPenghasilan extends BaseWidget
{
    protected function getStats(): array
    {
        // Data bulan ini
        $bulanIni = Pesanan::where('status_pembayaran', 'lunas')
            ->whereMonth('waktu_pesan', now()->month)
            ->whereYear('waktu_pesan', now()->year);

        // Data bulan lalu
        $bulanLalu = Pesanan::where('status_pembayaran', 'lunas')
            ->whereMonth('waktu_pesan', now()->subMonth()->month)
            ->whereYear('waktu_pesan', now()->subMonth()->year);

        // Hitung pemasukan bulan ini
        $pemasukanBulanIni = $bulanIni->sum('total_harga');
        $pemasukanBulanLalu = $bulanLalu->sum('total_harga');
        $selisihPemasukan = $pemasukanBulanIni - $pemasukanBulanLalu;
        $persentasePemasukan = $pemasukanBulanLalu > 0 ? (($selisihPemasukan / $pemasukanBulanLalu) * 100) : 0;

        // Hitung pengeluaran (modal) bulan ini
        $pengeluaranBulanIni = $bulanIni->with('details.menu')->get()->sum(function($pesanan) {
            return $pesanan->details->sum(function($detail) {
                return $detail->jumlah * $detail->menu->harga_modal;
            });
        });

        $pengeluaranBulanLalu = $bulanLalu->with('details.menu')->get()->sum(function($pesanan) {
            return $pesanan->details->sum(function($detail) {
                return $detail->jumlah * $detail->menu->harga_modal;
            });
        });

        $selisihPengeluaran = $pengeluaranBulanIni - $pengeluaranBulanLalu;
        $persentasePengeluaran = $pengeluaranBulanLalu > 0 ? (($selisihPengeluaran / $pengeluaranBulanLalu) * 100) : 0;

        // Hitung untung bersih
        $untungBulanIni = $pemasukanBulanIni - $pengeluaranBulanIni - $bulanIni->sum('pajak');
        $untungBulanLalu = $pemasukanBulanLalu - $pengeluaranBulanLalu - $bulanLalu->sum('pajak');
        $selisihUntung = $untungBulanIni - $untungBulanLalu;
        $persentaseUntung = $untungBulanLalu > 0 ? (($selisihUntung / $untungBulanLalu) * 100) : 0;

        return [
            Stat::make('Pemasukan Bulan Ini', 'Rp ' . number_format($pemasukanBulanIni, 0, ',', '.'))
                ->description(($selisihPemasukan >= 0 ? 'Naik ' : 'Turun ') . 'Rp ' . number_format(abs($selisihPemasukan), 0, ',', '.') . ' (' . number_format(abs($persentasePemasukan), 1) . '%)')
                ->descriptionIcon($selisihPemasukan >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($selisihPemasukan >= 0 ? 'success' : 'danger'),

            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($pengeluaranBulanIni, 0, ',', '.'))
                ->description(($selisihPengeluaran >= 0 ? 'Naik ' : 'Turun ') . 'Rp ' . number_format(abs($selisihPengeluaran), 0, ',', '.') . ' (' . number_format(abs($persentasePengeluaran), 1) . '%)')
                ->descriptionIcon($selisihPengeluaran >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($selisihPengeluaran >= 0 ? 'danger' : 'success'),

            Stat::make('Untung Bersih', 'Rp ' . number_format($untungBulanIni, 0, ',', '.'))
                ->description(($selisihUntung >= 0 ? 'Naik ' : 'Turun ') . 'Rp ' . number_format(abs($selisihUntung), 0, ',', '.') . ' (' . number_format(abs($persentaseUntung), 1) . '%)')
                ->descriptionIcon($selisihUntung >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($selisihUntung >= 0 ? 'success' : 'danger'),
        ];
    }
}