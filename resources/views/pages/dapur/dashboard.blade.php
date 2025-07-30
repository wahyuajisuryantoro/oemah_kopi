@extends('layouts.app')
@section('content')
    <div class="container-fluid p-4">
        <!-- Header Dashboard -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary bg-gradient">
                    <div class="card-body py-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="text-white mb-0">{{ $title }}</h3>
                                <p class="text-white-50 mb-0">{{ now()->format('l, d F Y') }}</p>
                            </div>
                            <div class="text-white text-end">
                                <h4 class="mb-0" id="realtime-clock">00:00:00</h4>
                                <small>Waktu Server</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Menunggu</h6>
                                <h2 class="mb-0">{{ $stats['menunggu'] }}</h2>
                            </div>
                            <i class="ri-timer-line ri-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Diproses</h6>
                                <h2 class="mb-0">{{ $stats['diproses'] }}</h2>
                            </div>
                            <i class="ri-restaurant-2-line ri-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Selesai Hari Ini</h6>
                                <h2 class="mb-0">{{ $stats['selesai'] }}</h2>
                            </div>
                            <i class="ri-check-double-line ri-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Pesanan -->
        <div class="row">
            <!-- Pesanan Menunggu -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-warning text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pesanan Menunggu</h5>
                            <span
                                class="badge bg-white text-warning">{{ $antrian_pesanan->where('status_antrian', 'menunggu')->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row" id="waiting-orders">
                            @forelse($antrian_pesanan->where('status_antrian', 'menunggu') as $antrian)
                                <div class="col-12 mb-3 mt-2">
                                    <div class="card order-card border-warning">
                                        <div
                                            class="card-header bg-warning text-white d-flex justify-content-between align-items-center py-2">
                                            <div>
                                                <h6 class="mb-0">
                                                    #{{ str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT) }} -
                                                    {{ $antrian->pesanan->atas_nama }}</h6>
                                                <small>Meja {{ $antrian->pesanan->meja->nomor_meja }}</small>
                                            </div>
                                            <div class="text-end">
                                                <div class="timer" data-created="{{ $antrian->waktu_masuk_antrian }}">00:00
                                                </div>
                                                <small>Menunggu</small>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <h6 class="mb-3">Daftar Pesanan:</h6>
                                                    <ul class="list-group">
                                                        @forelse($antrian->pesanan->details ?? [] as $detail)
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <strong>{{ $detail->menu->nama_menu ?? 'Menu tidak ditemukan' }}</strong>
                                                                    @if ($detail->catatan_khusus)
                                                                        <br><small
                                                                            class="text-muted">{{ $detail->catatan_khusus }}</small>
                                                                    @endif
                                                                </div>
                                                                <span
                                                                    class="badge bg-primary rounded-pill">{{ $detail->jumlah }}</span>
                                                            </li>
                                                        @empty
                                                            <li class="list-group-item text-muted">Tidak ada detail menu
                                                            </li>
                                                        @endforelse
                                                    </ul>
                                                </div>
                                                <div class="col-md-4 text-end mt-2">
                                                    <button class="btn btn-primary w-100"
                                                        onclick="updateStatus('{{ $antrian->pesanan->id }}', 'diproses')">
                                                        <i class="ri-play-fill"></i> Proses
                                                    </button>
                                                </div>
                                            </div>
                                            @if ($antrian->pesanan->catatan)
                                                <div class="mt-3">
                                                    <div class="alert alert-info mb-0">
                                                        <i class="ri-information-line me-2"></i>
                                                        {{ $antrian->pesanan->catatan }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="ri-restaurant-line ri-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">Tidak ada pesanan yang menunggu</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pesanan Diproses -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Sedang Diproses</h5>
                            <span
                                class="badge bg-white text-primary">{{ $antrian_pesanan->where('status_antrian', 'diproses')->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row" id="processing-orders">
                            @forelse($antrian_pesanan->where('status_antrian', 'diproses') as $antrian)
                                <div class="col-12 mb-3 mt-2">
                                    <div class="card order-card border-primary">
                                        <div
                                            class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                                            <div>
                                                <h6 class="mb-0">
                                                    #{{ str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT) }} -
                                                    {{ $antrian->pesanan->atas_nama }}</h6>
                                                <small>Meja {{ $antrian->pesanan->meja->nomor_meja }}</small>
                                            </div>
                                            <div class="text-end">
                                                <div class="timer" data-created="{{ $antrian->waktu_masuk_antrian }}">
                                                    00:00</div>
                                                <small>Diproses</small>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <h6 class="mb-3">Daftar Pesanan:</h6>
                                                    <ul class="list-group">
                                                        @forelse ($antrian->pesanan->details ?? [] as $detail)
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                                <div>
                                                                    <span
                                                                        class="fw-bold">{{ $detail->menu->nama_menu }}</span>
                                                                    @if ($detail->catatan_khusus)
                                                                        <br><small
                                                                            class="text-muted">{{ $detail->catatan_khusus }}</small>
                                                                    @endif
                                                                </div>
                                                                <span
                                                                    class="badge bg-primary rounded-pill">{{ $detail->jumlah }}</span>
                                                            </li>
                                                        @empty
                                                            <li class="list-group-item text-muted">Tidak ada detail pesanan
                                                            </li>
                                                        @endforelse
                                                    </ul>
                                                </div>
                                                <div class="col-md-4 text-end mt-2">
                                                    <button class="btn btn-success w-100"
                                                        onclick="updateStatus('{{ $antrian->pesanan->id }}', 'selesai')">
                                                        <i class="ri-check-line"></i> Selesai
                                                    </button>
                                                </div>
                                            </div>
                                            @if ($antrian->pesanan->catatan)
                                                <div class="mt-3">
                                                    <div class="alert alert-info mb-0">
                                                        <i class="ri-information-line me-2"></i>
                                                        {{ $antrian->pesanan->catatan }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="ri-restaurant-line ri-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">Tidak ada pesanan yang sedang diproses</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            document.getElementById('realtime-clock').textContent = timeString;
        }

        function updateTimers() {
            document.querySelectorAll('.timer').forEach(timer => {
                const created = new Date(timer.dataset.created);
                const now = new Date();
                const diff = Math.floor((now - created) / 1000);

                const minutes = Math.floor(diff / 60);
                const seconds = diff % 60;

                timer.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

                if (minutes >= 15) {
                    timer.classList.add('text-danger');
                }
            });
        }

        function updateStatus(pesananId, status) {
            Swal.fire({
                title: 'Konfirmasi',
                text: `Apakah anda yakin ingin mengubah status pesanan menjadi ${status}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    fetch(`/dapur/pesanan/${pesananId}/status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                status: status
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sukses',
                                    text: 'Status pesanan berhasil diupdate',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                throw new Error(data.message);
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', error.message, 'error');
                        });
                }
            });
        }

        // Set interval untuk update jam dan timer
        setInterval(updateClock, 1000);
        setInterval(updateTimers, 1000);

        // Initial call
        updateClock();
        updateTimers();

        // Auto refresh setiap 30 detik
        setInterval(() => {
            fetch('/dapur/antrian-data')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload hanya jika ada perubahan data
                        if (JSON.stringify(currentData) !== JSON.stringify(data.data)) {
                            location.reload();
                        }
                    }
                })
                .catch(console.error);
        }, 30000);
    </script>
@endsection
