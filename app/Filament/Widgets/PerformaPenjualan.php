<?php

namespace App\Filament\Widgets;

use App\Models\Pesanan;
use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PerformaPenjualan extends ChartWidget
{
   protected static ?string $heading = 'Performa Penjualan Per Bulan';
    protected static ?int $sort = 3;

    public ?string $filter = 'year';

    public function getHeading(): string
    {
        return 'Performa Penjualan Per Bulan';
    }

    protected function getFilters(): ?array
    {
        return [
            'year' => 'Tahun Ini',
            'last_year' => 'Tahun Lalu',
            'all' => 'Semua Data',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        
        // Setup tanggal berdasarkan filter
        switch ($activeFilter) {
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            case 'last_year':
                $startDate = now()->subYear()->startOfYear();
                $endDate = now()->subYear()->endOfYear();
                break;
            case 'all':
                $startDate = Pesanan::min('waktu_pesan') ? Carbon::parse(Pesanan::min('waktu_pesan'))->startOfYear() : now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            default:
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
        }

        // Data penjualan per bulan
        $salesData = Pesanan::where('status_pembayaran', 'lunas')
            ->whereBetween('waktu_pesan', [$startDate, $endDate])
            ->select(
                DB::raw('MONTH(waktu_pesan) as month'),
                DB::raw('SUM(total_harga) as total_sales'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy(DB::raw('MONTH(waktu_pesan)'))
            ->orderBy(DB::raw('MONTH(waktu_pesan)'))
            ->get();

        // Data profit per bulan
        $profitData = Pesanan::with('details.menu')
            ->where('status_pembayaran', 'lunas')
            ->whereBetween('waktu_pesan', [$startDate, $endDate])
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->waktu_pesan)->month;
            })
            ->map(function($orders) {
                return $orders->sum(function($order) {
                    $totalModal = $order->details->sum(function($detail) {
                        return $detail->jumlah * $detail->menu->harga_modal;
                    });
                    return ($order->total_harga - $order->pajak) - $totalModal;
                });
            });

        // Siapkan array untuk 12 bulan
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $salesArray = array_fill(0, 12, 0);
        $ordersArray = array_fill(0, 12, 0);
        $profitArray = array_fill(0, 12, 0);

        // Isi data penjualan
        foreach ($salesData as $data) {
            $salesArray[$data->month - 1] = $data->total_sales;
            $ordersArray[$data->month - 1] = $data->total_orders;
        }

        // Isi data profit
        foreach ($profitData as $month => $profit) {
            $profitArray[$month - 1] = $profit;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan (Rp)',
                    'data' => $salesArray,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Jumlah Pesanan',
                    'data' => $ordersArray,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
                [
                    'label' => 'Profit (Rp)',
                    'data' => $profitArray,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
