@extends('layouts.customer')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="ri-payment-line me-2"></i>
                Pembayaran Pesanan
            </h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Meja:</strong> {{ $meja->nomor_meja }}</p>
                    <p><strong>Atas Nama:</strong> {{ $pesanan->atas_nama }}</p>
                    <p><strong>Jumlah Orang:</strong> {{ $pesanan->jumlah_pelanggan }} orang</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Order ID:</strong> {{ $pesanan->order_id }}</p>
                    <p><strong>Waktu Pesan:</strong> {{ $pesanan->waktu_pesan_formatted }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-warning">{{ ucfirst($pesanan->status) }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Pesanan -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="ri-restaurant-line me-2"></i>
                Detail Pesanan
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menuItems as $item)
                        <tr>
                            <td>
                                <strong>{{ $item['nama_menu'] }}</strong>
                                @if($item['diskon'] > 0)
                                    <br><small class="text-danger">Diskon {{ $item['diskon'] }}%</small>
                                @endif
                            </td>
                            <td class="text-center">{{ $item['jumlah'] }}</td>
                            <td class="text-end">
                                @if($item['diskon'] > 0)
                                    <span class="text-decoration-line-through text-muted small">Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</span><br>
                                    <span class="text-success">Rp {{ number_format($item['harga_satuan'] - ($item['harga_satuan'] * $item['diskon'] / 100), 0, ',', '.') }}</span>
                                @else
                                    Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="text-end">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
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
                        <tr class="table-primary">
                            <th colspan="3">Total</th>
                            <th class="text-end">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($pesanan->catatan)
            <div class="alert alert-info">
                <strong>Catatan:</strong> {{ $pesanan->catatan }}
            </div>
            @endif
        </div>
    </div>

    <!-- Payment Button -->
    <div class="card">
        <div class="card-body text-center">
            <h5 class="mb-3">Silakan lakukan pembayaran</h5>
            <button class="btn btn-primary btn-lg" id="pay-button">
                <i class="ri-credit-card-line me-2"></i>
                Bayar Sekarang - Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
            </button>
            
            <div class="mt-3">
                <small class="text-muted">
                    <i class="ri-shield-check-line me-1"></i>
                    Pembayaran aman dengan Midtrans
                </small>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5>Memproses Pembayaran...</h5>
                    <p class="text-muted">Mohon tunggu, kami sedang memproses pembayaran Anda.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js') }}"></script>
<script>
document.getElementById('pay-button').addEventListener('click', function () {
    snap.pay('{{ $snapToken }}', {
        onSuccess: function(result) {
            console.log('Payment success:', result);
            showLoadingModal();
            updatePaymentStatus(result, 'success');
        },
        onPending: function(result) {
            console.log('Payment pending:', result);
            showLoadingModal();
            updatePaymentStatus(result, 'pending');
        },
        onError: function(result) {
            console.log('Payment error:', result);
            showLoadingModal();
            updatePaymentStatus(result, 'error');
        },
        onClose: function() {
            console.log('Payment popup closed');
            alert('Pembayaran dibatalkan. Silakan coba lagi jika diperlukan.');
        }
    });
});

function showLoadingModal() {
    const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
    modal.show();
}

function hideLoadingModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('loadingModal'));
    if (modal) {
        modal.hide();
    }
}

function updatePaymentStatus(result, type) {
    // Kirim data ke server untuk update status
    fetch('{{ route("payment.update.status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            order_id: result.order_id,
            transaction_status: result.transaction_status,
            payment_type: result.payment_type,
            fraud_status: result.fraud_status
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingModal();
        
        if (data.success) {
            console.log('Status updated:', data);
            
            // Redirect berdasarkan response
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                // Fallback redirect
                if (type === 'success') {
                    window.location.href = "{{ route('payment.success') }}?order_id={{ $pesanan->order_id }}";
                } else if (type === 'pending') {
                    window.location.href = "{{ route('payment.pending') }}?order_id={{ $pesanan->order_id }}";
                } else {
                    window.location.href = "{{ route('payment.error') }}?order_id={{ $pesanan->order_id }}";
                }
            }
        } else {
            console.error('Failed to update status:', data);
            alert('Terjadi kesalahan saat memproses pembayaran. Silakan hubungi customer service.');
        }
    })
    .catch(error => {
        hideLoadingModal();
        console.error('Error:', error);
        alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
    });
}
</script>
@endsection