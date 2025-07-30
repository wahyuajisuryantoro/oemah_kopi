@extends('layouts.customer')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Pending Message -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="ri-time-line text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="text-warning mb-3">Pembayaran Sedang Diproses</h2>
                    <p class="lead">Pembayaran Anda sedang dalam proses verifikasi.</p>
                    <p class="text-muted">Mohon tunggu beberapa saat hingga pembayaran dikonfirmasi.</p>
                    
                    <div class="mt-4">
                        <button class="btn btn-primary" onclick="checkPaymentStatus()">
                            <i class="ri-refresh-line me-2"></i>
                            Cek Status Pembayaran
                        </button>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="ri-file-list-3-line me-2"></i>
                        Detail Pesanan
                    </h5>
                </div>
                <div class="card-body">
                    @if($pesanan)
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Order ID:</strong> {{ $pesanan->order_id ?? 'Belum tersedia' }}
                        </div>
                        <div class="col-sm-6">
                            <strong>Meja:</strong> {{ $pesanan->meja->nomor_meja ?? 'N/A' }}
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
                            <strong>Waktu Pesan:</strong> 
                            @if($pesanan->waktu_pesan instanceof \Carbon\Carbon)
                                {{ $pesanan->waktu_pesan->format('d M Y, H:i') }}
                            @else
                                {{ date('d M Y, H:i', strtotime($pesanan->waktu_pesan)) }}
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <strong>Status:</strong> 
                            <span class="badge bg-warning">{{ ucfirst($pesanan->status) }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Status Pembayaran:</strong>
                            @if($pesanan->status_pembayaran === 'lunas')
                                <span class="badge bg-success">Lunas</span>
                            @else
                                <span class="badge bg-warning">Menunggu Pembayaran</span>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <strong>Metode Pembayaran:</strong> 
                            <span class="text-capitalize">{{ str_replace('_', ' ', $pesanan->metode_pembayaran) }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Total Pembayaran:</strong> 
                            <span class="h5 text-primary">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    @if($pesanan->catatan)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Catatan:</strong>
                            <p class="text-muted mb-0">{{ $pesanan->catatan }}</p>
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="text-center text-muted">
                        <p>Data pesanan tidak ditemukan</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Instructions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri-information-line me-2"></i>
                        Informasi Penting
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="ri-lightbulb-line me-2"></i>Yang Perlu Anda Lakukan:</h6>
                        <ul class="mb-0">
                            <li>Pastikan pembayaran sudah dilakukan melalui aplikasi/metode yang dipilih</li>
                            <li>Tunggu notifikasi konfirmasi pembayaran</li>
                            <li>Jika pembayaran berhasil, Anda akan diarahkan ke halaman konfirmasi</li>
                            <li>Hubungi staff jika ada kendala dalam pembayaran</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="ri-alert-line me-2"></i>Perhatian:</h6>
                        <p class="mb-0">Jika pembayaran tidak selesai dalam 15 menit, pesanan akan otomatis dibatalkan.</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center mb-5">
                <button class="btn btn-outline-primary me-2" onclick="checkPaymentStatus()">
                    <i class="ri-refresh-line me-1"></i>
                    Refresh Status
                </button>
                <a href="tel:081234567890" class="btn btn-outline-secondary">
                    <i class="ri-phone-line me-1"></i>
                    Hubungi Staff
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function checkPaymentStatus() {
    const btn = document.querySelector('button[onclick="checkPaymentStatus()"]');
    const originalText = btn.innerHTML;
    
    // Show loading
    btn.innerHTML = '<i class="ri-loader-4-line me-2 animate-spin"></i>Mengecek Status...';
    btn.disabled = true;
    
    // Reload page after short delay
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

// Auto refresh setiap 30 detik
let autoRefreshInterval = setInterval(function() {
    checkPaymentStatus();
}, 30000);

// Stop auto refresh jika user tidak aktif (tab tidak fokus)
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        clearInterval(autoRefreshInterval);
    } else {
        autoRefreshInterval = setInterval(function() {
            checkPaymentStatus();
        }, 30000);
    }
});

// Tambahan untuk handle jika pembayaran sudah berhasil
@if(isset($pesanan) && $pesanan->status_pembayaran === 'lunas')
    setTimeout(() => {
        window.location.href = "{{ route('payment.success', ['order_id' => $pesanan->order_id ?? '']) }}";
    }, 2000);
@endif
</script>
@endsection