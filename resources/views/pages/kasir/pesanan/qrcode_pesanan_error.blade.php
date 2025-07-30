@extends('layouts.customer')

@section('content')
<div class="container my-5">
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h4 class="mb-0">Meja Tidak Tersedia</h4>
        </div>
        <div class="card-body text-center">
            <div class="mb-4">
                <i class="ri-error-warning-line" style="font-size: 5rem; color: #dc3545;"></i>
            </div>
            <h3>Maaf, Meja {{ $meja->nomor_meja }} sedang tidak tersedia</h3>
            <p class="mb-4">{{ $message ?? 'Meja ini sedang digunakan oleh pelanggan lain.' }}</p>
            <p>Silakan menghubungi pelayan untuk bantuan.</p>
        </div>
    </div>
</div>
@endsection