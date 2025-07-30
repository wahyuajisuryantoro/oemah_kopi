@extends('layouts.customer')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Error Message -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="ri-error-warning-line text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="text-danger mb-3">Pembayaran Gagal</h2>
                    <p class="lead">Terjadi kesalahan dalam proses pembayaran.</p>
                    
                    @if(isset($error_message) && $error_message)
                    <div class="alert alert-danger">
                        <strong>Pesan Error:</strong> {{ $error_message }}
                    </div>
                    @endif
                    
                    <p class="text-muted">Silakan coba lagi atau hubungi staff untuk bantuan.</p>
                </div>
            </div>

            <!-- Order Details -->
            @if($pesanan)
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
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
                            <strong>Waktu Pesan:</strong> {{ $pesanan->waktu_pesan->format('d M Y, H:i') }}
                        </div>
                        <div class="col-sm-6">
                            <strong>Status:</strong> 
                            <span class="badge bg-danger">{{ ucfirst($pesanan->status) }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Total yang Harus Dibayar:</strong> 
                            <span class="h5 text-primary">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Solutions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri-lightbulb-line me-2"></i>
                        Solusi yang Dapat Dicoba
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="ri-information-line me-2"></i>Kemungkinan Penyebab:</h6>
                        <ul>
                            <li>Koneksi internet tidak stabil</li>
                            <li>Saldo tidak mencukupi</li>
                            <li>Kartu kredit/debit bermasalah</li>
                            <li>Pembayaran dibatalkan</li>
                            <li>Waktu pembayaran habis</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="ri-check-line me-2"></i>Yang Dapat Anda Lakukan:</h6>
                        <ol>
                            <li>Pastikan koneksi internet stabil</li>
                            <li>Periksa saldo rekening/e-wallet</li>
                            <li>Coba metode pembayaran lain</li>
                            <li>Ulangi proses pemesanan</li>
                            <li>Hubungi customer service bank/e-wallet</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection