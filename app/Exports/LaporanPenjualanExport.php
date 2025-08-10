<?php

namespace App\Exports;

use App\Models\Pesanan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class LaporanPenjualanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return Pesanan::with(['details.menu', 'meja'])
            ->where('status_pembayaran', 'lunas')
            ->whereBetween('waktu_pesan', [
                $this->startDate . ' 00:00:00',
                $this->endDate . ' 23:59:59'
            ])
            ->orderBy('waktu_pesan', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Tanggal',
            'Waktu', 
            'Customer',
            'Meja',
            'Status',
            'Metode Bayar',
            'Subtotal (Rp)',
            'Pajak (Rp)',
            'Total (Rp)',
            'Modal (Rp)',
            'Profit (Rp)',
            'Detail Menu'
        ];
    }

    public function map($pesanan): array
    {
        // Hitung total modal
        $totalModal = $pesanan->details->sum(function($detail) {
            return $detail->jumlah * $detail->menu->harga_modal;
        });

        // Hitung profit
        $profit = $pesanan->total_harga - $pesanan->pajak - $totalModal;

        // Detail menu
        $detailMenu = $pesanan->details->map(function($detail) {
            return $detail->menu->nama_menu . ' (' . $detail->jumlah . 'x)';
        })->implode(', ');

        return [
            $pesanan->order_id,
            Carbon::parse($pesanan->waktu_pesan)->format('d/m/Y'),
            Carbon::parse($pesanan->waktu_pesan)->format('H:i'),
            $pesanan->atas_nama,
            $pesanan->meja->nomor_meja ?? '-',
            ucfirst($pesanan->status),
            ucfirst(str_replace('_', ' ', $pesanan->metode_pembayaran)),
            $pesanan->total_harga - $pesanan->pajak,
            $pesanan->pajak,
            $pesanan->total_harga,
            $totalModal,
            $profit,
            $detailMenu
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Order ID
            'B' => 12, // Tanggal
            'C' => 8,  // Waktu
            'D' => 15, // Customer
            'E' => 8,  // Meja
            'F' => 12, // Status
            'G' => 15, // Metode Bayar
            'H' => 15, // Subtotal
            'I' => 12, // Pajak
            'J' => 15, // Total
            'K' => 12, // Modal
            'L' => 12, // Profit
            'M' => 40, // Detail Menu
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header style
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => 'E2E8F0']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
            // Data rows
            'A:M' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
            // Number columns alignment
            'H:L' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0'
                ],
            ],
        ];
    }
}