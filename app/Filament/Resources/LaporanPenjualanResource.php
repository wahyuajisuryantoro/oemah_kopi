<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanPenjualanResource\Pages;
use App\Models\Pesanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class LaporanPenjualanResource extends Resource
{
    protected static ?string $model = Pesanan::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('waktu_pesan')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('atas_nama')
                    ->label('Customer')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('meja.nomor_meja')
                    ->label('Meja'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'menunggu',
                        'primary' => 'diproses', 
                        'success' => 'selesai',
                        'danger' => 'dibatalkan',
                    ]),
                
                Tables\Columns\BadgeColumn::make('status_pembayaran')
                    ->label('Pembayaran')
                    ->colors([
                        'warning' => 'menunggu_pembayaran',
                        'success' => 'lunas',
                    ]),
                
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('profit')
                    ->label('Profit')
                    ->money('IDR')
                    ->getStateUsing(function ($record) {
                        $totalModal = $record->details->sum(function($detail) {
                            return $detail->jumlah * $detail->menu->harga_modal;
                        });
                        return $record->total_harga - $record->pajak - $totalModal;
                    }),
            ])
            ->filters([
                Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('waktu_pesan', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('waktu_pesan', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make()
                        ->withFilename('laporan-penjualan-' . date('Y-m-d'))
                        ->withColumns([
                            Column::make('order_id')->heading('Order ID'),
                            Column::make('waktu_pesan')
                                ->heading('Tanggal')
                                ->formatStateUsing(fn($state) => Carbon::parse($state)->format('d/m/Y H:i')),
                            Column::make('atas_nama')->heading('Customer'),
                            Column::make('meja.nomor_meja')->heading('Meja'),
                            Column::make('status')->heading('Status'),
                            Column::make('metode_pembayaran')->heading('Metode Bayar'),
                            Column::make('subtotal')
                                ->heading('Subtotal (Rp)')
                                ->getStateUsing(fn($record) => $record->total_harga - $record->pajak),
                            Column::make('pajak')->heading('Pajak (Rp)'),
                            Column::make('total_harga')->heading('Total (Rp)'),
                            Column::make('modal')
                                ->heading('Modal (Rp)')
                                ->getStateUsing(function($record) {
                                    return $record->details->sum(function($detail) {
                                        return $detail->jumlah * $detail->menu->harga_modal;
                                    });
                                }),
                            Column::make('profit')
                                ->heading('Profit (Rp)')
                                ->getStateUsing(function($record) {
                                    $totalModal = $record->details->sum(function($detail) {
                                        return $detail->jumlah * $detail->menu->harga_modal;
                                    });
                                    return ($record->total_harga - $record->pajak) - $totalModal;
                                }),
                            Column::make('detail_menu')
                                ->heading('Detail Menu')
                                ->getStateUsing(function($record) {
                                    return $record->details->map(function($detail) {
                                        return $detail->menu->nama_menu . ' (' . $detail->jumlah . 'x)';
                                    })->implode(', ');
                                }),
                        ])
                ])
            ])
            ->defaultSort('waktu_pesan', 'desc')
            ->query(function () {
                return Pesanan::with(['details.menu', 'meja'])
                    ->where('status_pembayaran', 'lunas');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanPenjualans::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}