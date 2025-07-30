<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PesananAntrian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;

class Kasir_DashboardController extends Controller
{
    public function index()
    {
        try {
            $title = 'Dashboard Kasir';
            $id_kasir = Auth::id();

            // Antrian pesanan tetap sama
            $antrian_pesanan = PesananAntrian::with(['pesanan' => function ($query) use ($id_kasir) {
                $query->where('id_kasir', $id_kasir)
                    ->with(['meja', 'kasir', 'detail.menu']);
            }])
                ->whereHas('pesanan', function ($query) use ($id_kasir) {
                    $query->where('id_kasir', $id_kasir);
                })
                ->whereDate('created_at', today())
                ->orderBy('nomor_antrian', 'asc')
                ->get();

            // Query untuk meja yang tersedia
            $meja_tersedia = Meja::where(function ($query) {
                $query->where('tersedia', true)
                    ->orWhereHas('pesanan_aktif', function ($q) {
                        $q->whereIn('status', ['menunggu', 'diproses']);
                    });
            })
                ->withCount(['pesanan_aktif as total_pelanggan' => function ($query) {
                    $query->select(DB::raw('COALESCE(SUM(jumlah_pelanggan), 0)'))
                        ->whereIn('status', ['menunggu', 'diproses']);
                }])
                ->get()
                ->filter(function ($meja) {
                    // Filter meja yang masih memiliki kapasitas tersedia
                    return ($meja->kapasitas - ($meja->total_pelanggan ?? 0)) > 0;
                })
                ->map(function ($meja) {
                    // Hitung kapasitas yang tersedia
                    $meja->kapasitas_tersedia = $meja->kapasitas - ($meja->total_pelanggan ?? 0);
                    return $meja;
                });

            // Menu tetap sama
            $menu = Menu::where('tersedia', true)
                ->where('stok', '>', 0)
                ->with('kategori')
                ->get();

            Log::info('Halaman pesanan diakses', [
                'kasir_id' => $id_kasir,
                'total_antrian' => $antrian_pesanan->count(),
                'meja_tersedia' => $meja_tersedia->count(),
                'menu_tersedia' => $menu->count()
            ]);

            return view('pages.kasir.dashboard', compact('title', 'antrian_pesanan', 'meja_tersedia', 'menu'));
        } catch (\Exception $e) {
            Log::error('Error pada halaman pesanan', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Alert::error('Error', 'Terjadi kesalahan saat memuat halaman pesanan');
            return redirect()->back();
        }
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'id_meja' => 'required|exists:tbl_meja,id',
                'atas_nama' => 'required|string|max:255',
                'jumlah_pelanggan' => 'required|integer|min:1',
                'items' => 'required|array',
                'items.*.id_menu' => 'required|exists:tbl_menu,id',
                'items.*.jumlah' => 'required|integer|min:1',
                'catatan' => 'nullable|string'
            ]);

            Log::info('Memulai proses pembuatan pesanan', [
                'user_id' => Auth::id(),
                'request_data' => $validated
            ]);
            $meja = Meja::findOrFail($validated['id_meja']);
            if ($meja->kapasitas < $validated['jumlah_pelanggan']) {
                throw new \Exception("Jumlah pelanggan melebihi kapasitas meja ({$meja->kapasitas} orang)");
            }
            $meja->kapasitas -= $validated['jumlah_pelanggan'];
            $meja->save();

            $total_harga = 0;
            $items_detail = [];

            foreach ($validated['items'] as $item) {
                $menu = Menu::findOrFail($item['id_menu']);
                if ($menu->stok < $item['jumlah']) {
                    throw new \Exception("Stok {$menu->nama_menu} tidak mencukupi");
                }

                $harga_satuan = $menu->harga_jual;
                $diskon = $menu->diskon ?? 0;
                $subtotal = ($harga_satuan - ($harga_satuan * $diskon / 100)) * $item['jumlah'];
                $total_harga += $subtotal;
                $menu->stok -= $item['jumlah'];
                $menu->save();

                $items_detail[] = [
                    'id_menu' => $menu->id,
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $harga_satuan,
                    'diskon' => $diskon,
                    'subtotal' => $subtotal,
                    'catatan_khusus' => $item['catatan'] ?? null
                ];
            }
            $pajak = $total_harga * 0.1;
            $pesanan = Pesanan::create([
                'id_meja' => $validated['id_meja'],
                'id_kasir' => Auth::id(),
                'atas_nama' => $validated['atas_nama'],
                'waktu_pesan' => Carbon::now(),
                'status' => 'menunggu',
                'total_harga' => $total_harga,
                'pajak' => $pajak,
                'catatan' => $validated['catatan'] ?? null,
                'metode_pembayaran' => 'tunai'
            ]);
            foreach ($items_detail as $detail) {
                $pesanan->detail()->create($detail);
            }
            Meja::where('id', $validated['id_meja'])->update(['tersedia' => false]);
            $nomor_antrian = PesananAntrian::whereDate('created_at', Carbon::today())
                ->max('nomor_antrian') ?? 0;

            $antrian = PesananAntrian::create([
                'id_pesanan' => $pesanan->id,
                'nomor_antrian' => $nomor_antrian + 1,
                'status_antrian' => 'menunggu',
                'waktu_masuk_antrian' => Carbon::now()
            ]);

            DB::commit();
            Log::info('Pesanan berhasil dibuat', [
                'user_id' => Auth::id(),
                'pesanan_id' => $pesanan->id,
                'nomor_antrian' => $antrian->nomor_antrian
            ]);

            Alert::success('Sukses', 'Pesanan berhasil dibuat dengan nomor antrian ' . $antrian->nomor_antrian);
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
                'data' => [
                    'nomor_antrian' => $antrian->nomor_antrian,
                    'pesanan' => $pesanan->load('detail.menu')
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error saat membuat pesanan', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Alert::error('Error', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function updateStatus(Request $request, Pesanan $pesanan)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'status' => 'required|in:diproses,siap,diantar,selesai,dibatalkan'
            ]);

            Log::info('Memulai update status pesanan', [
                'user_id' => Auth::id(),
                'pesanan_id' => $pesanan->id,
                'status_baru' => $validated['status'],
                'jumlah_pelanggan' => $pesanan->jumlah_pelanggan
            ]);

            $pesanan->status = $validated['status'];
            $pesanan->save();

            // Update status antrian sesuai kondisi
            if ($validated['status'] == 'diproses') {
                $pesanan->antrian->status_antrian = 'diproses';
                $pesanan->antrian->save();
            }
            // Jika status selesai atau dibatalkan
            elseif (in_array($validated['status'], ['selesai', 'dibatalkan'])) {
                // Update status antrian
                $pesanan->antrian->status_antrian = 'selesai';
                $pesanan->antrian->waktu_keluar_antrian = Carbon::now();
                $pesanan->antrian->save();

                // Ambil data meja dan kapasitas awal
                $meja = $pesanan->meja;
                $kapasitasAwal = $meja->kapasitas;

                // Kembalikan kapasitas meja
                $meja->kapasitas += $pesanan->jumlah_pelanggan;
                $meja->tersedia = true;
                $meja->save();

                Log::info('Kapasitas meja dikembalikan', [
                    'meja_id' => $meja->id,
                    'kapasitas_awal' => $kapasitasAwal,
                    'jumlah_dikembalikan' => $pesanan->jumlah_pelanggan,
                    'kapasitas_akhir' => $meja->kapasitas
                ]);
            }

            DB::commit();

            Log::info('Status pesanan berhasil diupdate', [
                'user_id' => Auth::id(),
                'pesanan_id' => $pesanan->id,
                'status_baru' => $validated['status'],
                'meja_id' => $pesanan->meja->id,
                'kapasitas_meja' => $pesanan->meja->kapasitas
            ]);

            Alert::success('Sukses', 'Status pesanan berhasil diupdate');

            return response()->json([
                'success' => true,
                'message' => 'Status pesanan berhasil diupdate',
                'data' => $pesanan->load(['antrian', 'meja'])
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error saat update status pesanan', [
                'user_id' => Auth::id(),
                'pesanan_id' => $pesanan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Alert::error('Error', 'Gagal mengupdate status pesanan');

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getAntrianDapur()
    {
        try {
            $antrian = PesananAntrian::with(['pesanan' => function ($query) {
                $query->with(['meja', 'detail.menu'])
                    ->whereIn('status', ['menunggu', 'diproses']);
            }])
                ->where('status_antrian', '!=', 'selesai')
                ->orderBy('nomor_antrian', 'asc')
                ->get();

            Log::info('Antrian dapur diakses', [
                'user_id' => Auth::id(),
                'total_antrian' => $antrian->count()
            ]);

            return response()->json($antrian);
        } catch (\Exception $e) {
            Log::error('Error saat mengambil antrian dapur', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Alert::error('Error', 'Gagal mengambil data antrian dapur');

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data antrian'
            ], 500);
        }
    }
}
