@extends('layouts.app')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
    <h2 class="mb-4">Manajemen Meja</h2>
    <div class="row mb-4">
        <div class="col-md-6 col-xl-4">
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addNewMejaModal">
                <i class="bi bi-plus-lg"></i> Tambah Meja
            </button>
        </div>
    </div>

    <div class="card mb-6">
        <div class="row">
            @foreach ($mejas as $meja)
                <div class="col-md-6 col-xl-2 mb-4 mt-2">
                    <div class="card {{ $meja->tersedia ? 'bg-success' : 'bg-danger' }} text-white">
                        <div class="card-header text-white text-center fs-4">
                            Meja {{ $meja->nomor_meja }}
                        </div>

                        <div class="card-body">
                            <h5 class="card-title text-white">{{ $meja->tersedia ? 'Tersedia' : 'Terisi' }}</h5>
                            <p class="card-text">Kapasitas: {{ $meja->kapasitas }} orang</p>
                            @if ($meja->lokasi)
                                <p class="card-text">Lokasi: {{ $meja->lokasi }}</p>
                            @endif
                            <div class="mt-3">
                                @if ($meja->tersedia)
                                    <!-- Tombol Edit -->
                                    <button type="button" class="btn btn-icon btn-sm btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#editMejaModal{{ $meja->id }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Edit Meja">
                                        <i class="ri-edit-line"></i>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <button type="button" class="btn btn-icon btn-sm btn-danger delete-meja"
                                        data-id="{{ $meja->id }}" data-status="{{ $meja->tersedia }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Meja">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                @endif

                                <!-- Tombol Toggle Status -->
                                <form action="{{ route('meja.toggle-status', $meja) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="btn btn-icon btn-sm {{ $meja->tersedia ? 'btn-warning' : 'btn-success' }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $meja->tersedia ? 'Set Terisi' : 'Set Tersedia' }}">
                                        <i class="{{ $meja->tersedia ? 'ri-close-circle-line' : 'ri-check-line' }}"></i>
                                    </button>
                                </form>

                                <a href="{{ route('meja.show-qrcode', $meja) }}" class="btn btn-icon btn-sm btn-info" 
                                data-bs-toggle="tooltip" data-bs-placement="top" title="QR Code Meja">
                                 <i class="ri-qr-code-line"></i>
                             </a>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Edit Meja -->
                    <div class="modal fade" id="editMejaModal{{ $meja->id }}" tabindex="-1"
                        aria-labelledby="editMejaModalLabel{{ $meja->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-simple modal-edit-meja">
                            <div class="modal-content">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                                <div class="modal-body p-0">
                                    <div class="text-center mb-6">
                                        <h4 class="mb-2">Edit Meja</h4>
                                        <p>Edit informasi meja</p>
                                    </div>
                                    <form class="row g-5" action="{{ route('meja.update', $meja) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="col-12">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" id="nomor_meja_edit{{ $meja->id }}"
                                                    name="nomor_meja" class="form-control" placeholder="Nomor Meja"
                                                    value="{{ old('nomor_meja', $meja->nomor_meja) }}" required>
                                                <label for="nomor_meja_edit{{ $meja->id }}">Nomor Meja</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating form-floating-outline">
                                                <input type="number" id="kapasitas_edit{{ $meja->id }}"
                                                    name="kapasitas" class="form-control" placeholder="Kapasitas"
                                                    value="{{ old('kapasitas', $meja->kapasitas) }}" required>
                                                <label for="kapasitas_edit{{ $meja->id }}">Kapasitas</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" id="lokasi_edit{{ $meja->id }}" name="lokasi"
                                                    class="form-control" placeholder="Lokasi"
                                                    value="{{ old('lokasi', $meja->lokasi) }}">
                                                <label for="lokasi_edit{{ $meja->id }}">Lokasi</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="tersedia" value="0">
                                                <input type="checkbox" class="form-check-input"
                                                    id="tersedia_edit{{ $meja->id }}" name="tersedia" value="1"
                                                    {{ $meja->tersedia ? 'checked' : '' }}>
                                                <label for="tersedia_edit{{ $meja->id }}"
                                                    class="text-heading">Tersedia</label>
                                            </div>
                                        </div>
                                        <div class="col-12 d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                                            <button type="submit" class="btn btn-primary">Perbarui Meja</button>
                                            <button type="reset" class="btn btn-outline-secondary btn-reset"
                                                data-bs-dismiss="modal">Batal</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="modal fade" id="addNewMejaModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-simple modal-add-new-meja">
                <div class="modal-content">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="modal-body p-0">
                        <div class="text-center mb-6">
                            <h4 class="mb-2">Tambah Meja Baru</h4>
                            <p>Tambahkan meja baru untuk layanan restoran</p>
                        </div>
                        <form id="addNewMejaForm" class="row g-5" action="{{ route('meja.store') }}" method="POST">
                            @csrf
                            <div class="col-12">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" id="nomor_meja" name="nomor_meja" class="form-control"
                                        placeholder="Nomor Meja" required>
                                    <label for="nomor_meja">Nomor Meja</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating form-floating-outline">
                                    <input type="number" id="kapasitas" name="kapasitas" class="form-control"
                                        placeholder="Kapasitas" required>
                                    <label for="kapasitas">Kapasitas</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" id="lokasi" name="lokasi" class="form-control"
                                        placeholder="Lokasi">
                                    <label for="lokasi">Lokasi</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="tersedia" value="0">
                                    <input type="checkbox" class="form-check-input" id="tersedia" name="tersedia"
                                        value="1" checked>
                                    <label for="tersedia" class="text-heading">Tersedia</label>
                                </div>

                            </div>
                            <div class="col-12 d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                                <button type="submit" class="btn btn-primary">Tambah Meja</button>
                                <button type="reset" class="btn btn-outline-secondary btn-reset"
                                    data-bs-dismiss="modal">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal QR Code -->
        <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="qrCodeModalLabel">QR Code Meja</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div id="qrCodeImage" class="mb-3">
                            <!-- QR code akan ditampilkan di sini -->
                        </div>
                        <p id="qrCodeCaption" class="mb-3"></p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <a id="downloadQrCode" href="#" download class="btn btn-primary">
                            <i class="ri-download-line"></i> Download QR Code
                        </a>
                        <a id="regenerateQrCode" href="#" class="btn btn-warning">
                            <i class="ri-refresh-line"></i> Regenerate QR Code
                        </a>
                        <a id="viewOrderPage" href="#" target="_blank" class="btn btn-info">
                            <i class="ri-external-link-line"></i> Lihat Halaman Pemesanan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.delete-meja').on('click', function() {
                var mejaId = $(this).data('id');
                var mejaStatus = $(this).data('status');

                if (mejaStatus == 0) {
                    Swal.fire({
                        title: 'Peringatan!',
                        text: 'Meja ini sedang terisi. Tidak dapat dihapus.',
                        icon: 'warning',
                        confirmButtonText: 'Mengerti'
                    });
                } else {
                    Swal.fire({
                        title: 'Anda yakin?',
                        text: "Meja ini akan dihapus secara permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/meja/' + mejaId,
                                type: 'DELETE',
                                data: {
                                    "_token": "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    Swal.fire(
                                        'Terhapus!',
                                        'Meja berhasil dihapus.',
                                        'success'
                                    ).then(() => {
                                        location.reload();
                                    });
                                },
                                error: function(xhr) {
                                    Swal.fire(
                                        'Error!',
                                        'Terjadi kesalahan saat menghapus meja.',
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Handler untuk tombol Lihat QR Code
            $('.show-qrcode').on('click', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');

                // Ambil data QR code melalui AJAX
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        // Isi modal dengan data QR code
                        $('#qrCodeImage').html('<img src="' + response.qrCodeUrl +
                            '" class="img-fluid" style="max-width: 300px;">');
                        $('#qrCodeCaption').text(
                            'Scan QR code ini untuk melakukan pemesanan di Meja ' + response
                            .nomor_meja);
                        $('#downloadQrCode').attr('href', response.qrCodeUrl);
                        $('#downloadQrCode').attr('download', 'QR_Code_Meja_' + response
                            .nomor_meja + '.png');
                        $('#regenerateQrCode').attr('href', response.regenerateUrl);
                        $('#viewOrderPage').attr('href', response.orderPageUrl);

                        // Tampilkan modal
                        $('#qrCodeModal').modal('show');
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Terjadi kesalahan saat mengambil data QR code.',
                            'error'
                        );
                    }
                });
            });

            $('#regenerateQrCode').on('click', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');

                Swal.fire({
                    title: 'Anda yakin?',
                    text: "QR code lama akan diganti dengan yang baru!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, regenerate!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                $('#qrCodeImage').html('<img src="' + response
                                    .qrCodeUrl +
                                    '" class="img-fluid" style="max-width: 300px;">'
                                    );
                                $('#viewOrderPage').attr('href', response.orderPageUrl);

                                Swal.fire(
                                    'Berhasil!',
                                    'QR code berhasil di-regenerate.',
                                    'success'
                                );
                            },
                            error: function() {
                                Swal.fire(
                                    'Error!',
                                    'Terjadi kesalahan saat regenerate QR code.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
