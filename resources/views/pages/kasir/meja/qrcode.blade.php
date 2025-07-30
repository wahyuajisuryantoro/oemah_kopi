@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-2 container-p-y">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $title }}</h5>
                    <a href="{{ route('meja.index') }}" class="btn btn-sm btn-secondary">
                        <i class="ri-arrow-left-line"></i> Kembali
                    </a>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4 qr-container">
                        @if($meja->qr_code_path && file_exists(public_path($meja->qr_code_path)))
                            <img src="{{ asset($meja->qr_code_path) }}?v={{ time() }}" alt="QR Code Meja {{ $meja->nomor_meja }}" class="img-fluid qr-image" style="max-width: 300px;">
                        @elseif(isset($qrCodeSvg))
                            <div class="mx-auto qr-svg" style="max-width: 300px;">
                                {!! $qrCodeSvg !!}
                            </div>
                        @endif
                    </div>
                    
                    <h4 class="qr-title">Meja {{ $meja->nomor_meja }}</h4>
                    <p class="mb-4 qr-url"><small>URL Pemesanan: <a href="{{ $orderUrl ?? url('/pemesanan/meja/' . $meja->id . '?token=' . $meja->qr_code_token) }}" target="_blank">{{ $orderUrl ?? url('/pemesanan/meja/' . $meja->id . '?token=' . $meja->qr_code_token) }}</a></small></p>
                    
                    <div class="mt-3 mb-3 action-buttons">
                        <button onclick="printQrCode()" class="btn btn-success">
                            <i class="ri-printer-line"></i> Cetak QR Code
                        </button>
                        
                        <button onclick="viewQrCodeInNewTab()" class="btn btn-info">
                            <i class="ri-external-link-line"></i> Lihat di Tab Baru
                        </button>
                        
                        @if(!$meja->qr_code_path || !file_exists(public_path($meja->qr_code_path)))
                            <a href="{{ route('meja.generate-qrcode', $meja) }}" class="btn btn-primary">
                                <i class="ri-save-line"></i> Simpan QR Code
                            </a>
                        @endif
                        
                        <a href="{{ route('meja.regenerate-qrcode', $meja) }}" class="btn btn-warning regenerate-qr" 
                           data-id="{{ $meja->id }}">
                            <i class="ri-refresh-line"></i> Buat Ulang QR Code
                        </a>
                    </div>
                    
                    @if($meja->qr_code_path && file_exists(public_path($meja->qr_code_path)))
                        <div class="alert alert-success mt-3 status-alert">
                            <p class="mb-0"><i class="ri-check-line"></i> QR code telah disimpan ke server.</p>
                        </div>
                    @else
                        <div class="alert alert-info mt-3 status-alert">
                            <p class="mb-0"><i class="ri-information-line"></i> QR code belum disimpan ke server. Klik "Simpan QR Code" untuk menyimpannya.</p>
                        </div>
                    @endif
                    
                    <div class="alert alert-info mt-3 info-alert">
                        <p class="mb-0"><i class="ri-information-line"></i> Cetak QR code ini dan tempel di Meja {{ $meja->nomor_meja }} agar pelanggan dapat melakukan pemesanan dengan scan QR code.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('style')
<style>
@media print {
    /* Hide semua elemen */
    body * {
        visibility: hidden;
    }
    
    /* Show hanya elemen yang diperlukan untuk print */
    .qr-container,
    .qr-container *,
    .qr-title,
    .qr-url {
        visibility: visible;
    }
    
    /* Reset body untuk print */
    body {
        margin: 0;
        padding: 20px;
        background: white;
    }
    
    /* Container QR Code */
    .qr-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        text-align: center;
    }
    
    /* QR Code Image/SVG styling */
    .qr-image, .qr-svg {
        width: 250px !important;
        height: 250px !important;
        max-width: 250px !important;
        margin: 0 auto;
        display: block;
    }
    
    /* SVG spesifik styling */
    .qr-svg svg {
        width: 250px !important;
        height: 250px !important;
    }
    
    /* Title styling */
    .qr-title {
        position: absolute;
        bottom: -80px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 18px;
        font-weight: bold;
        margin: 0;
        white-space: nowrap;
    }
    
    /* URL styling */
    .qr-url {
        position: absolute;
        bottom: -110px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 10px;
        margin: 0;
        white-space: nowrap;
        width: 100%;
        text-align: center;
    }
    
    /* Hide elements yang tidak diperlukan */
    .action-buttons,
    .status-alert,
    .info-alert,
    .btn,
    .alert,
    .card-header {
        display: none !important;
    }
    
    /* Set page margins */
    @page {
        margin: 1cm;
        size: A4;
    }
}

/* Normal view styling */
@media screen {
    .qr-container {
        margin-bottom: 20px;
    }
    
    .qr-title {
        margin-top: 10px;
        margin-bottom: 15px;
    }
}
</style>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Fungsi untuk regenerasi QR code melalui konfirmasi
        $('.regenerate-qr').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            
            Swal.fire({
                title: 'Anda yakin?',
                text: "QR code lama akan diganti dengan yang baru dan tidak akan berfungsi lagi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, buat ulang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
    
    // Fungsi print yang lebih baik
    function printQrCode() {
        // Beri delay sedikit untuk memastikan styling print sudah apply
        setTimeout(function() {
            window.print();
        }, 100);
    }
    
    // Fungsi untuk buka QR code di tab baru
    function viewQrCodeInNewTab() {
        // Buat HTML untuk tab baru yang hanya menampilkan QR code
        const qrContainer = document.querySelector('.qr-container').cloneNode(true);
        const qrTitle = document.querySelector('.qr-title').textContent;
        const qrUrl = document.querySelector('.qr-url').textContent;
        
        const newTabContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>QR Code - ${qrTitle}</title>
                <style>
                    body {
                        margin: 0;
                        padding: 20px;
                        background: white;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        min-height: 100vh;
                        font-family: Arial, sans-serif;
                    }
                    .qr-container {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .qr-container img, .qr-container svg {
                        width: 400px;
                        height: 400px;
                        max-width: 400px;
                    }
                    h2 {
                        margin: 20px 0 10px 0;
                        font-size: 24px;
                    }
                    p {
                        margin: 10px 0;
                        font-size: 12px;
                        word-break: break-all;
                        max-width: 400px;
                    }
                    .print-btn {
                        margin-top: 20px;
                        padding: 10px 20px;
                        background: #007bff;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                    }
                    .print-btn:hover {
                        background: #0056b3;
                    }
                    @media print {
                        .print-btn { display: none; }
                        body { min-height: auto; }
                    }
                </style>
            </head>
            <body>
                <div class="qr-container">
                    ${qrContainer.innerHTML}
                </div>
                <h2>${qrTitle}</h2>
                <p>${qrUrl}</p>
                <button class="print-btn" onclick="window.print()">Cetak QR Code</button>
            </body>
            </html>
        `;
        
        const newTab = window.open();
        newTab.document.write(newTabContent);
        newTab.document.close();
    }
</script>
@endsection