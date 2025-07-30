@extends('layouts.customer')

@section('content')
    <div class="container">
        <!-- Info Meja -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="ri-restaurant-line" style="font-size: 2rem; color: #6f42c1;"></i>
                    </div>
                    <div class="col-8">
                        <h4 class="mb-1">Meja {{ $meja->nomor_meja }}</h4>
                        <p class="text-muted mb-0">
                            <i class="ri-user-line me-1"></i>Kapasitas {{ $meja->kapasitas }} orang
                        </p>
                        <p class="text-muted mb-0">
                            <i class="ri-map-pin-line me-1"></i>{{ $meja->lokasi ?? 'Area Utama' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Debug Information (hapus di production) -->
        @if (config('app.debug'))
            <div class="alert alert-info">
                <strong>Debug Info:</strong>
                <br>Kategori Menu: {{ count($kategoriMenu ?? []) }}
                @foreach ($kategoriMenu ?? [] as $kategori)
                    <br>- {{ $kategori->nama_kategori }}: {{ $kategori->menus ? $kategori->menus->count() : 0 }} items
                @endforeach
            </div>
        @endif

        <!-- Form Pemesanan -->
       <form id="orderForm" action="{{ route('payment.create') }}?token={{ $meja->qr_code_token }}" method="POST">
            @csrf
            <input type="hidden" name="id_meja" value="{{ $meja->id }}">

            <!-- Informasi Pelanggan -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri-user-3-line me-2"></i>
                        Informasi Pemesanan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="atas_nama" class="form-label">Atas Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="atas_nama" name="atas_nama" required
                                placeholder="Masukkan nama">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jumlah_pelanggan" class="form-label">Jumlah Orang <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" id="jumlah_pelanggan" name="jumlah_pelanggan" required>
                                <option value="">Pilih jumlah</option>
                                @for ($i = 1; $i <= $meja->kapasitas; $i++)
                                    <option value="{{ $i }}">{{ $i }} Orang</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="catatan" class="form-label">Catatan Khusus</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kategori Menu -->
            <div class="d-flex overflow-auto mb-3" style="gap: 0.5rem;">
                <button type="button" class="btn btn-primary category-tab active" data-category="all">
                    <i class="ri-restaurant-line me-1"></i>Semua
                </button>
                @foreach ($kategoriMenu as $kategori)
                    <button type="button" class="btn btn-outline-primary category-tab" data-category="{{ $kategori->id }}">
                        <i class="ri-cup-line me-1"></i>{{ $kategori->nama_kategori }}
                    </button>
                @endforeach
            </div>

            <!-- Menu Items -->
            @foreach ($kategoriMenu as $kategori)
                <div class="card menu-section mb-3" data-category="{{ $kategori->id }}">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ri-cup-line me-2"></i>
                            {{ $kategori->nama_kategori }}
                        </h5>
                        <small class="text-muted">{{ $kategori->menus ? $kategori->menus->count() : 0 }} item
                            tersedia</small>
                    </div>
                    <div class="card-body">
                        @forelse($kategori->menus ?? [] as $menu)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-3 col-sm-4">
                                            @if ($menu->gambar_url)
                                                <img src="{{ asset('storage/' . $menu->gambar_url) }}"
                                                    alt="{{ $menu->nama_menu }}" class="img-fluid rounded"
                                                    style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                    style="width: 60px; height: 60px;">
                                                    <i class="ri-restaurant-line"
                                                        style="font-size: 1.5rem; color: #6f42c1;"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-5 col-sm-3">
                                            <h6 class="mb-1">{{ $menu->nama_menu }}</h6>
                                            <p class="text-muted small mb-2 d-none d-sm-block">
                                                {{ Str::limit($menu->deskripsi ?? 'Menu lezat dari Oemah Kopi', 30) }}</p>
                                            <div>
                                                @if ($menu->diskon && $menu->diskon > 0)
                                                    <span
                                                        class="badge bg-danger me-1 small">-{{ $menu->diskon }}%</span><br
                                                        class="d-sm-none">
                                                    <span class="text-decoration-line-through text-muted small me-1">Rp
                                                        {{ number_format($menu->harga_jual, 0, ',', '.') }}</span>
                                                    <span class="badge bg-warning text-dark small">Rp
                                                        {{ number_format($menu->harga_jual - ($menu->harga_jual * $menu->diskon) / 100, 0, ',', '.') }}</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Rp
                                                        {{ number_format($menu->harga_jual, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <button type="button" class="btn btn-sm btn-danger p-3 flex-shrink-0"
                                                    onclick="decreaseQuantity({{ $menu->id }})">-</button>
                                                <input type="number"
                                                    class="form-control form-control-sm text-center mx-2 fw-bold flex-shrink-0"
                                                    id="qty_{{ $menu->id }}" name="menu[{{ $menu->id }}][jumlah]"
                                                    value="0" min="0" readonly>
                                                <button type="button" class="btn btn-sm btn-success p-3 flex-shrink-0"
                                                    onclick="increaseQuantity({{ $menu->id }})">+</button>
                                            </div>
                                            <input type="hidden" name="menu[{{ $menu->id }}][catatan]"
                                                value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="ri-restaurant-line" style="font-size: 3rem; color: #dee2e6;"></i>
                                <p class="text-muted mt-2">Belum ada menu di kategori ini</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach

            <!-- Order Summary -->
            <div class="card" id="orderSummary" style="display: none;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Ringkasan Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong>Rp <span id="subtotalPrice">0</span></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pajak (10%):</span>
                        <strong>Rp <span id="taxPrice">0</span></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="h6">Total:</span>
                        <strong class="h5 text-primary">Rp <span id="finalPrice">0</span></strong>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid gap-2 mt-3 mb-5">
                <button type="submit" class="btn btn-success btn-lg" id="submitOrder" disabled>
                    <i class="ri-shopping-cart-line me-2"></i>
                    Pesan Sekarang (<span id="totalItemsBtn">0</span> item)
                </button>
            </div>
        </form>
    </div>

    <!-- Floating Total -->
    <div class="position-fixed bottom-0 end-0 m-3" id="floatingTotal" style="display: none;">
        <div class="bg-primary text-white p-3 rounded">
            <div class="d-flex align-items-center">
                <span id="totalItems">0</span> item -
                <strong class="ms-1">Rp <span id="totalPrice">0</span></strong>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let orderData = {};
        let menuPrices = {};

        // Load menu prices
        @foreach ($kategoriMenu as $kategori)
            @foreach ($kategori->menus as $menu)
                menuPrices[{{ $menu->id }}] = {
                    price: {{ $menu->harga_jual }},
                    discount: {{ $menu->diskon ?? 0 }}
                };
            @endforeach
        @endforeach

        function increaseQuantity(menuId) {
            const input = document.getElementById('qty_' + menuId);
            const currentValue = parseInt(input.value) || 0;
            input.value = currentValue + 1;
            updateOrderData(menuId, currentValue + 1);
            updateOrderSummary();
        }

        function decreaseQuantity(menuId) {
            const input = document.getElementById('qty_' + menuId);
            const currentValue = parseInt(input.value) || 0;
            if (currentValue > 0) {
                input.value = currentValue - 1;
                updateOrderData(menuId, currentValue - 1);
                updateOrderSummary();
            }
        }

        function updateOrderData(menuId, quantity) {
            if (quantity > 0) {
                orderData[menuId] = quantity;
            } else {
                delete orderData[menuId];
            }
        }

        function updateOrderSummary() {
            let totalItems = 0;
            let subtotal = 0;

            for (const [menuId, quantity] of Object.entries(orderData)) {
                totalItems += quantity;
                const menuPrice = menuPrices[menuId];
                if (menuPrice) {
                    const discountedPrice = menuPrice.price - (menuPrice.price * menuPrice.discount / 100);
                    subtotal += discountedPrice * quantity;
                }
            }

            const tax = subtotal * 0.1;
            const total = subtotal + tax;

            // Update displays - check if element exists first
            const totalItemsEl = document.getElementById('totalItems');
            const totalItemsBtnEl = document.getElementById('totalItemsBtn');
            const totalPriceEl = document.getElementById('totalPrice');
            const subtotalPriceEl = document.getElementById('subtotalPrice');
            const taxPriceEl = document.getElementById('taxPrice');
            const finalPriceEl = document.getElementById('finalPrice');

            if (totalItemsEl) totalItemsEl.textContent = totalItems;
            if (totalItemsBtnEl) totalItemsBtnEl.textContent = totalItems;
            if (totalPriceEl) totalPriceEl.textContent = formatPrice(total);
            if (subtotalPriceEl) subtotalPriceEl.textContent = formatPrice(subtotal);
            if (taxPriceEl) taxPriceEl.textContent = formatPrice(tax);
            if (finalPriceEl) finalPriceEl.textContent = formatPrice(total);

            // Show/hide elements
            const orderSummary = document.getElementById('orderSummary');
            const floatingTotal = document.getElementById('floatingTotal');
            const submitBtn = document.getElementById('submitOrder');

            if (totalItems > 0) {
                if (orderSummary) orderSummary.style.display = 'block';
                if (floatingTotal) floatingTotal.style.display = 'block';
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="ri-shopping-cart-line me-2"></i>Pesan Sekarang (' + totalItems +
                        ' item)';
                }
            } else {
                if (orderSummary) orderSummary.style.display = 'none';
                if (floatingTotal) floatingTotal.style.display = 'none';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="ri-shopping-cart-line me-2"></i>Pilih Menu Dulu';
                }
            }
        }

        function formatPrice(amount) {
            return new Intl.NumberFormat('id-ID').format(Math.round(amount));
        }

        // Form validation
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const atasNama = document.getElementById('atas_nama').value.trim();
            const jumlahPelanggan = document.getElementById('jumlah_pelanggan').value;

            if (!atasNama) {
                e.preventDefault();
                alert('Mohon isi nama pemesan');
                return;
            }

            if (!jumlahPelanggan) {
                e.preventDefault();
                alert('Mohon pilih jumlah orang');
                return;
            }

            if (Object.keys(orderData).length === 0) {
                e.preventDefault();
                alert('Mohon pilih minimal satu menu');
                return;
            }
        });

        // Initialize
        updateOrderSummary();
    </script>
@endsection
