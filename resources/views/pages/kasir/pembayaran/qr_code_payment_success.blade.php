@extends('layouts.customer')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Success Message -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="ri-check-double-line text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="text-success mb-3">Pembayaran Berhasil!</h2>
                    <p class="lead">Terima kasih, pembayaran Anda telah berhasil diproses.</p>
                    <p class="text-muted">Pesanan Anda sedang diproses oleh dapur.</p>
                </div>
            </div>

            <!-- Order Details -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="ri-file-list-3-line me-2"></i>
                        Detail Pesanan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Order ID:</strong> {{ $pesanan->order_id }}
                        </div>
                        <div class="col-sm-6">
                            <strong>Meja:</strong> {{ $pesanan->meja->nomor_meja }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Atas Nama:</strong> {{ $pesanan->atas_nama }}
                        </div>
                        <div class="col-sm-6">
                            <strong>Jumlah Orang:</strong> {{ $pesanan->jumlah_pelanggan }} orang
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Waktu Pesan:</strong> {{ \Carbon\Carbon::parse($pesanan->waktu_pesan)->format('d M Y, H:i') }}
                        </div>
                        <div class="col-sm-6">
                            <strong>Status:</strong> 
                            <span class="badge bg-primary">{{ ucfirst($pesanan->status) }}</span>
                        </div>
                    </div>

                    <h6 class="mt-4 mb-3">Menu yang Dipesan:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pesanan->details as $detail)
                                <tr>
                                    <td>{{ $detail->menu->nama_menu }}</td>
                                    <td class="text-center">{{ $detail->jumlah }}</td>
                                    <td class="text-end">
                                        @if($detail->diskon > 0)
                                            <span class="text-decoration-line-through text-muted small">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</span><br>
                                            <span class="text-success">Rp {{ number_format($detail->harga_satuan - ($detail->harga_satuan * $detail->diskon / 100), 0, ',', '.') }}</span>
                                        @else
                                            Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Subtotal</th>
                                    <th class="text-end">Rp {{ number_format($pesanan->total_harga - $pesanan->pajak, 0, ',', '.') }}</th>
                                </tr>
                                <tr>
                                    <th colspan="3">Pajak (10%)</th>
                                    <th class="text-end">Rp {{ number_format($pesanan->pajak, 0, ',', '.') }}</th>
                                </tr>
                                <tr class="table-success">
                                    <th colspan="3">Total Dibayar</th>
                                    <th class="text-end">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($pesanan->catatan)
                    <div class="alert alert-info mt-3">
                        <strong>Catatan:</strong> {{ $pesanan->catatan }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Next Steps -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri-information-line me-2"></i>
                        Langkah Selanjutnya
                    </h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li class="mb-2">Silakan menuju ke <strong>Meja {{ $pesanan->meja->nomor_meja }}</strong></li>
                        <li class="mb-2">Pesanan Anda sedang diproses oleh dapur</li>
                        <li class="mb-2">Estimasi waktu penyajian: <strong>15-30 menit</strong></li>
                        <li class="mb-2">Staff akan mengantarkan pesanan ke meja Anda</li>
                    </ol>
                    
                    <div class="alert alert-warning">
                        <i class="ri-time-line me-2"></i>
                        <strong>Penting:</strong> Harap tunjukkan halaman ini kepada staff jika diperlukan.
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center mb-5">
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="ri-printer-line me-1"></i>
                    Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>
@endsection