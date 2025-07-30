@extends('layouts.customer')

@section('content')
<div class="container my-5">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Pesanan Berhasil Dikirim</h4>
        </div>
        <div class="card-body text-center">
            <div class="mb-4">
                <i class="ri-check-double-line" style="font-size: 5rem; color: #28a745;"></i>
            </div>
            <h3>Terima Kasih, {{ $pesanan->atas_nama }}!</h3>
            <p class="mb-2">Pesanan Anda sedang diproses.</p>
            <p class="mb-4">Nomor Pesanan: <strong>{{ $pesanan->id }}</strong></p>
            
            <div class="alert alert-info">
                <p class="mb-0">Silakan tunggu. Pelayan kami akan segera mengantar pesanan Anda ke Meja {{ $meja->nomor_meja }}.</p>
            </div>
        </div>
    </div>
</div>
@endsection