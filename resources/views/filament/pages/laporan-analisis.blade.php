<x-filament-panels::page>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold">Laporan & Analisis</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Laporan Penjualan -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-bold mb-4">Laporan Penjualan</h3>
                <ul>
                    @foreach ($this->getLaporanPenjualan() as $laporan)
                        <li class="flex justify-between">
                            <span>{{ $laporan->tanggal }}</span>
                            <span>Rp{{ number_format($laporan->total, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Statistik Produk Terlaris -->
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-bold mb-4">Produk Terlaris</h3>
                <ul>
                    @foreach ($this->getProdukTerlaris() as $produk)
                        <li class="flex justify-between">
                            <span>{{ $produk->nama_menu }}</span>
                            <span>{{ $produk->total_terjual }} Terjual</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Performa Karyawan -->
            <div class="bg-white p-4 rounded-lg shadow col-span-1 md:col-span-2">
                <h3 class="text-lg font-bold mb-4">Performa Karyawan</h3>
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="border-b pb-2">Nama Karyawan</th>
                            <th class="border-b pb-2">Total Pesanan</th>
                            <th class="border-b pb-2">Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->getPerformaKaryawan() as $karyawan)
                            <tr>
                                <td class="border-b py-1">{{ $karyawan->nama_karyawan }}</td>
                                <td class="border-b py-1">{{ $karyawan->total_pesanan }}</td>
                                <td class="border-b py-1">Rp{{ number_format($karyawan->total_penjualan, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
