@extends('layouts.app')
@section('content')
    <div class="container py-4">
       
        <div class="d-flex align-items justify-content gap-4 mb-4">
            <h2 class="mb-4">Order Service</h2>
            <div class="avatar avatar-lg">
                <span class="avatar-initial rounded bg-label-primary" style="width: 48px; height: 48px;">
                    <i class="ri-time-line ri-2x"></i>
                </span>
            </div>
            <div class="text-start">
                <h3 class="mb-1 fw-bold" id="currentTime" style="font-size: 2.2rem; letter-spacing: 1px;"></h3>
                <h5 class="text-muted mb-0" id="currentDate" style="font-size: 1.1rem;"></h5>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-9 col-md-8 col-12">
                <!-- Antrian Pesanan -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Antrian Pesanan</h5>
                        <div>
                            <span class="badge bg-warning me-2">Menunggu:
                                {{ $antrian_pesanan->where('status_antrian', 'menunggu')->count() }}</span>
                            <span class="badge bg-primary me-2">Diproses:
                                {{ $antrian_pesanan->where('status_antrian', 'diproses')->count() }}</span>
                            <span class="badge bg-success">Selesai:
                                {{ $antrian_pesanan->where('status_antrian', 'selesai')->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="cardContainer" class="row">
                            @forelse($antrian_pesanan as $index => $antrian)
                                <div class="col-sm-6 col-lg-3 mb-3 antrian-card {{ $index >= 4 ? 'd-none' : '' }}">
                                    <div
                                        class="card card-border-shadow-{{ $antrian->status_antrian === 'menunggu'
                                            ? 'warning'
                                            : ($antrian->status_antrian === 'diproses'
                                                ? 'primary'
                                                : ($antrian->status_antrian === 'selesai'
                                                    ? 'success'
                                                    : 'secondary')) }} h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="avatar me-4">
                                                    <span
                                                        class="avatar-initial rounded-3 bg-label-{{ $antrian->status_antrian === 'menunggu'
                                                            ? 'warning'
                                                            : ($antrian->status_antrian === 'diproses'
                                                                ? 'primary'
                                                                : ($antrian->status_antrian === 'selesai'
                                                                    ? 'success'
                                                                    : 'secondary')) }}">
                                                        <i class="ri-restaurant-2-line ri-24px"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h5 class="mb-0">Antrian
                                                        #{{ str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT) }}</h5>
                                                    <small
                                                        class="text-{{ $antrian->status_antrian === 'menunggu'
                                                            ? 'warning'
                                                            : ($antrian->status_antrian === 'diproses'
                                                                ? 'primary'
                                                                : ($antrian->status_antrian === 'selesai'
                                                                    ? 'success'
                                                                    : 'secondary')) }}">
                                                        {{ ucfirst($antrian->status_antrian) }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <p class="mb-1">
                                                    <strong>Atas Nama:</strong> {{ $antrian->pesanan->atas_nama }}
                                                </p>
                                                <p class="mb-1">
                                                    <strong>Meja:</strong> {{ $antrian->pesanan->meja->nomor_meja }}
                                                </p>
                                                <p class="mb-1">
                                                    <strong>Total:</strong> Rp
                                                    {{ number_format($antrian->pesanan->total_harga, 0, ',', '.') }}
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        {{ $antrian->waktu_masuk_antrian->diffForHumans() }}
                                                    </small>
                                                    @if ($antrian->waktu_keluar_antrian)
                                                        <small class="text-success">
                                                            Selesai: {{ $antrian->waktu_keluar_antrian->format('H:i') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="ri-information-line me-2"></i>
                                        Belum ada pesanan yang dibuat hari ini
                                    </div>
                                </div>
                            @endforelse
                        </div>
                        @if (count($antrian_pesanan) > 4)
                            <div class="text-center mt-3">
                                <button id="toggleAntrian" class="btn btn-primary">
                                    Lihat Antrian Lain
                                </button>
                            </div>
                        @endif
                    </div>

                </div>
                <!-- Search and Filter Section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="searchMenu" placeholder="Cari menu...">
                                    <label for="searchMenu">Cari Menu</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <select class="form-select" id="filterKategori">
                                        <option value="">Semua Kategori</option>
                                        @foreach ($menu->pluck('kategori.nama_kategori')->unique() as $kategori)
                                            <option value="{{ $kategori }}">{{ $kategori }}</option>
                                        @endforeach
                                    </select>
                                    <label for="filterKategori">Filter Kategori</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Daftar Menu -->
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    @foreach ($menu as $item)
                        <div class="col">
                            <div class="card h-100">
                                @if ($item->gambar_url)
                                    <img class="card-img-top" src="{{ asset('storage/' . $item->gambar_url) }}"
                                        alt="{{ $item->nama_menu }}" style="height: 200px; object-fit: cover;" />
                                @else
                                    <div class="bg-secondary-subtle text-center py-5">
                                        <i class="ri-restaurant-2-line ri-3x"></i>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">{{ $item->nama_menu }}</h5>
                                        <span class="badge bg-label-primary">{{ $item->kategori->nama_kategori }}</span>
                                    </div>
                                    {{-- <p class="card-text text-muted small">{{ $item->deskripsi }}</p> --}}
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 text-primary">Rp
                                            {{ number_format($item->harga_jual, 0, ',', '.') }}</h6>
                                        <button class="btn btn-primary btn-sm" onclick="addToCart({{ $item->id }})">
                                            <i class="ri-add-line"></i> Tambah
                                        </button>
                                    </div>
                                    <small class="text-muted">Stok: {{ $item->stok }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-12">
                <form id="orderForm" action="{{ route('pesanan.store') }}" method="POST">
                    @csrf
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="form-floating form-floating-outline mb-3">
                                <input type="text" class="form-control" id="atas_nama" name="atas_nama" required
                                    placeholder="Nama Pemesan" />
                                <label for="atas_nama">Nama Pemesan</label>
                            </div>
                            <div class="mb-3">
                                <label for="jumlah_pelanggan" class="form-label">Jumlah Pelanggan</label>
                                <input type="number" class="form-control" id="jumlah_pelanggan" name="jumlah_pelanggan"
                                    required min="1" value="1" />
                                <small class="text-muted">Masukkan jumlah pelanggan sesuai kapasitas meja</small>
                            </div>
                            <div class="mb-3">
                                <label for="id_meja" class="form-label">Nomor Meja</label>
                                <select id="id_meja" name="id_meja" class="form-select" required>
                                    <option value="">Pilih Meja</option>
                                    @foreach ($meja_tersedia as $meja)
                                        <option value="{{ $meja->id }}"
                                            data-kapasitas="{{ $meja->kapasitas_tersedia }}">
                                            Meja {{ $meja->nomor_meja }}
                                            (Tersedia: {{ $meja->kapasitas_tersedia }} dari {{ $meja->kapasitas }} Orang)
                                            @if ($meja->total_pelanggan > 0)
                                                - Terisi: {{ $meja->total_pelanggan }} Orang
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    Menampilkan meja dengan kapasitas yang masih tersedia
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Keranjang Pesanan -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Pesanan</h5>
                        </div>
                        <div class="card-body">
                            <div id="cartItems">

                            </div>

                            <div class="bg-secondary-subtle rounded-3 p-3 mt-3">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Subtotal</span>
                                        <span class="fw-semibold" id="subtotal">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Pajak (10%)</span>
                                        <span class="fw-semibold" id="pajak">Rp 0</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Total</span>
                                        <span class="fw-bold text-primary" id="total">Rp 0</span>
                                    </div>
                                </div>

                                <textarea class="form-control mb-3" name="catatan" rows="2" placeholder="Catatan Pesanan (Opsional)"></textarea>

                                <button type="submit" class="btn btn-primary w-100" id="btnSubmit">
                                    Buat Pesanan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    {{-- Fungsi Pesanan --}}
    <script>
        let cart = [];
        const menus = @json($menu);

        function showAlert(type, message) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Sukses' : 'Error',
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        }

        function addToCart(menuId) {
            const menu = menus.find(m => m.id === menuId);
            if (!menu) return;

            const existingItem = cart.find(item => item.id_menu === menuId);
            if (existingItem) {
                if (existingItem.jumlah >= menu.stok) {
                    showAlert('error', `Stok ${menu.nama_menu} tidak mencukupi!`);
                    return;
                }
                existingItem.jumlah++;
            } else {
                cart.push({
                    id_menu: menuId,
                    nama_menu: menu.nama_menu,
                    harga: menu.harga_jual,
                    jumlah: 1
                });
            }
            updateCartDisplay();
        }

        function removeFromCart(menuId) {
            const menu = menus.find(m => m.id === menuId);
            Swal.fire({
                title: 'Konfirmasi',
                text: `Hapus ${menu.nama_menu} dari pesanan?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    cart = cart.filter(item => item.id_menu !== menuId);
                    updateCartDisplay();
                }
            });
        }

        function updateQuantity(menuId, increment) {
            const item = cart.find(item => item.id_menu === menuId);
            if (!item) return;

            const menu = menus.find(m => m.id === menuId);

            if (increment) {
                if (item.jumlah >= menu.stok) {
                    showAlert('error', `Stok ${menu.nama_menu} tidak mencukupi!`);
                    return;
                }
                item.jumlah++;
            } else {
                item.jumlah--;
                if (item.jumlah === 0) {
                    removeFromCart(menuId);
                    return;
                }
            }
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const cartContainer = document.getElementById('cartItems');
            cartContainer.innerHTML = '';

            let subtotal = 0;

            cart.forEach(item => {
                subtotal += item.harga * item.jumlah;

                cartContainer.innerHTML += `
            <div class="position-relative d-flex align-items-start mb-3 p-3 border rounded">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                        onclick="removeFromCart(${item.id_menu})">
                    <i class="ri-delete-bin-line"></i>
                </button>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${item.nama_menu}</h6>
                    <p class="text-primary fw-semibold mb-2">
                        Rp ${item.harga.toLocaleString('id-ID')}
                    </p>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-icon btn-primary btn-sm"
                                onclick="updateQuantity(${item.id_menu}, false)">
                            <i class="ri-subtract-line"></i>
                        </button>
                        <span class="mx-3 fw-semibold">${item.jumlah}</span>
                        <button type="button" class="btn btn-icon btn-primary btn-sm"
                                onclick="updateQuantity(${item.id_menu}, true)">
                            <i class="ri-add-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
            });
            const pajak = subtotal * 0.1;
            const total = subtotal + pajak;

            document.getElementById('subtotal').textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
            document.getElementById('pajak').textContent = `Rp ${pajak.toLocaleString('id-ID')}`;
            document.getElementById('total').textContent = `Rp ${total.toLocaleString('id-ID')}`;
            document.getElementById('btnSubmit').disabled = cart.length === 0;
        }
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (cart.length === 0) {
                showAlert('error', 'Keranjang masih kosong!');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Pesanan',
                text: 'Apakah anda yakin ingin membuat pesanan ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Buat Pesanan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitOrder(this);
                }
            });
        });
        document.getElementById('id_meja').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const kapasitasTersedia = parseInt(selectedOption.dataset.kapasitas);
            const jumlahPelangganInput = document.getElementById('jumlah_pelanggan');

            jumlahPelangganInput.max = kapasitasTersedia;

            if (parseInt(jumlahPelangganInput.value) > kapasitasTersedia) {
                jumlahPelangganInput.value = kapasitasTersedia;
                showAlert('warning',
                    `Jumlah pelanggan disesuaikan dengan kapasitas tersedia: ${kapasitasTersedia} orang`);
            }
        });
        document.getElementById('jumlah_pelanggan').addEventListener('input', function() {
            const mejaSelect = document.getElementById('id_meja');
            if (mejaSelect.value) {
                const selectedOption = mejaSelect.options[mejaSelect.selectedIndex];
                const kapasitasTersedia = parseInt(selectedOption.dataset.kapasitas);

                if (parseInt(this.value) > kapasitasTersedia) {
                    this.value = kapasitasTersedia;
                    showAlert('warning', `Maksimal ${kapasitasTersedia} orang untuk meja ini`);
                }
            }
        });

        function submitOrder(form) {
            const formData = {
                id_meja: document.getElementById('id_meja').value,
                atas_nama: document.getElementById('atas_nama').value,
                jumlah_pelanggan: document.getElementById('jumlah_pelanggan').value,
                catatan: document.querySelector('textarea[name="catatan"]').value,
                items: cart.map(item => ({
                    id_menu: item.id_menu,
                    jumlah: item.jumlah
                })),
                _token: document.querySelector('meta[name="csrf-token"]').content
            };

            Swal.fire({
                title: 'Memproses Pesanan',
                text: 'Mohon tunggu...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pesanan Berhasil',
                            text: `Nomor Antrian: ${data.data.nomor_antrian}`,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            cart = [];
                            updateCartDisplay();
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Terjadi kesalahan saat membuat pesanan'
                    });
                });
        }
        updateCartDisplay();
    </script>
    {{-- Fungsi Expand Card Antrian --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('toggleAntrian');
            const hiddenCards = document.querySelectorAll('.antrian-card.d-none');
            let isExpanded = false;

            if (toggleButton) {
                toggleButton.addEventListener('click', function() {
                    if (isExpanded) {
                        hiddenCards.forEach(card => card.classList.add('d-none'));
                        toggleButton.textContent = 'Lihat Antrian Lain';
                    } else {
                        hiddenCards.forEach(card => card.classList.remove('d-none'));
                        toggleButton.textContent = 'Sembunyikan Antrian';
                    }
                    isExpanded = !isExpanded;
                });
            }
        });
    </script>
    {{-- Fungsi Cari dan Fil --}}
    <script>
        let filteredMenus = [...menus];
        const menuContainer = document.querySelector('.row.row-cols-1.row-cols-md-3');
        function initializeMenuFilter() {
            const searchInput = document.getElementById('searchMenu');
            const kategoriFilter = document.getElementById('filterKategori');

            function filterMenus() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedKategori = kategoriFilter.value.toLowerCase();

                filteredMenus = menus.filter(menu => {
                    const matchSearch = menu.nama_menu.toLowerCase().includes(searchTerm);
                    const matchKategori = selectedKategori === '' || menu.kategori.nama_kategori.toLowerCase() ===
                        selectedKategori;
                    return matchSearch && matchKategori;
                });

                renderMenus();
            }

            function renderMenus() {
                menuContainer.innerHTML = filteredMenus.map(item => `
            <div class="col">
                <div class="card h-100">
                    ${item.gambar_url 
                        ? `<img class="card-img-top" src="${assetUrl}/${item.gambar_url}" 
                                alt="${item.nama_menu}" style="height: 200px; object-fit: cover;" />`
                        : `<div class="bg-secondary-subtle text-center py-5">
                                <i class="ri-restaurant-2-line ri-3x"></i>
                               </div>`
                    }
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">${item.nama_menu}</h5>
                            <span class="badge bg-label-primary">${item.kategori.nama_kategori}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-primary">Rp ${item.harga_jual.toLocaleString('id-ID')}</h6>
                            <button class="btn btn-primary btn-sm" onclick="addToCart(${item.id})">
                                <i class="ri-add-line"></i> Tambah
                            </button>
                        </div>
                        <small class="text-muted">Stok: ${item.stok}</small>
                    </div>
                </div>
            </div>
        `).join('');
            }
            searchInput.addEventListener('input', filterMenus);
            kategoriFilter.addEventListener('change', filterMenus);
            renderMenus();
        }
        document.addEventListener('DOMContentLoaded', function() {
            initializeMenuFilter();
        });
        const assetUrl = '{{ asset('storage') }}';
    </script>
    <script>
        function padZero(num) {
            return String(num).padStart(2, '0');
        }
    
        const hariIndonesia = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    
        const bulanIndonesia = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
        function updateClock() {
            const now = new Date();
            const hari = hariIndonesia[now.getDay()];
            const tanggal = now.getDate();
            const bulan = bulanIndonesia[now.getMonth()];
            const tahun = now.getFullYear();
            const jam = padZero(now.getHours());
            const menit = padZero(now.getMinutes());
            const detik = padZero(now.getSeconds());
            document.getElementById('currentTime').innerHTML = `${jam}:${menit}:${detik} <small class="text-muted">WIB</small>`;
            document.getElementById('currentDate').textContent = `${hari}, ${tanggal} ${bulan} ${tahun}`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
@endsection
