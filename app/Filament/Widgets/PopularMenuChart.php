<?php

namespace App\Filament\Widgets;

use App\Models\PesananDetail;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PopularMenuChart extends ChartWidget
{
    protected static ?string $heading = 'Menu Terpopuler';
    protected static ?int $sort = 3;

    public ?string $filter = 'month';

     protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari Ini',
            'week' => 'Minggu Ini',
            'month' => 'Bulan Ini',
            'year' => 'Tahun Ini',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        
        // Setup tanggal berdasarkan filter
        $query = PesananDetail::with(['menu', 'pesanan'])
            ->whereHas('pesanan', function($q) {
                $q->where('status_pembayaran', 'lunas');
            });

        switch ($activeFilter) {
            case 'today':
                $query->whereHas('pesanan', function($q) {
                    $q->whereDate('waktu_pesan', today());
                });
                break;
            case 'week':
                $query->whereHas('pesanan', function($q) {
                    $q->whereBetween('waktu_pesan', [now()->startOfWeek(), now()->endOfWeek()]);
                });
                break;
            case 'month':
                $query->whereHas('pesanan', function($q) {
                    $q->whereMonth('waktu_pesan', now()->month)
                      ->whereYear('waktu_pesan', now()->year);
                });
                break;
            case 'year':
                $query->whereHas('pesanan', function($q) {
                    $q->whereYear('waktu_pesan', now()->year);
                });
                break;
        }

        // Ambil top 5 menu terpopuler
        $menuData = $query->select('id_menu', DB::raw('SUM(jumlah) as total_terjual'))
            ->groupBy('id_menu')
            ->orderBy('total_terjual', 'desc')
            ->limit(5)
            ->with('menu')
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            'rgba(255, 99, 132, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 205, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
        ];

        foreach ($menuData as $index => $item) {
            $labels[] = $item->menu->nama_menu;
            $data[] = $item->total_terjual;
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ]
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
