<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\MenuKategori;
use Illuminate\Http\Request;
use App\Models\PesananDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class Kasir_PesananController extends Controller
{
    public function index()
    {
        $title = "Daftar Pesanan";
        $today = now()->format('Y-m-d');
        $stats = [
            'menunggu' => DB::table('tbl_pesanan')
                ->whereDate('waktu_pesan', $today)
                ->where('status', 'menunggu')
                ->count(),

            'diproses' => DB::table('tbl_pesanan')
                ->whereDate('waktu_pesan', $today)
                ->where('status', 'diproses')
                ->count(),

            'selesai' => DB::table('tbl_pesanan')
                ->whereDate('waktu_pesan', $today)
                ->where('status', 'selesai')
                ->count(),

            'total_pendapatan' => DB::table('tbl_pesanan')
                ->whereDate('waktu_pesan', $today)
                ->where('status', 'selesai')
                ->sum('total_harga'),
        ];
        return view('pages.kasir.pesanan.index', compact('title', 'stats'));
    }

    public function getDatatables()
    {
        $pesanan = DB::table('tbl_pesanan as p')
            ->join('tbl_meja as m', 'p.id_meja', '=', 'm.id')
            ->leftJoin('users as u', 'p.id_kasir', '=', 'u.id')
            ->select([
                'p.id',
                'p.atas_nama',
                'p.waktu_pesan',
                'p.status',
                'p.status_pembayaran',
                'p.total_harga',
                'p.metode_pembayaran',
                'm.nomor_meja',
                'u.name as kasir_name',
                DB::raw("DATE_FORMAT(p.waktu_pesan, '%Y-%m-%d') as tanggal"),
                DB::raw("DATE_FORMAT(p.waktu_pesan, '%H:%i') as waktu")
            ])
            ->orderBy('p.waktu_pesan', 'desc');

        return DataTables::of($pesanan)
            ->addIndexColumn()
            ->filter(function ($query) {
                if (request()->has('search') && request()->get('search')['value']) {
                    $searchValue = request()->get('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('p.atas_nama', 'like', "%{$searchValue}%")
                            ->orWhere('m.nomor_meja', 'like', "%{$searchValue}%")
                            ->orWhere('p.status', 'like', "%{$searchValue}%")
                            ->orWhere('p.status_pembayaran', 'like', "%{$searchValue}%");
                    });
                }
            })
            ->addColumn('waktu_pesan_format', function ($row) {
                return [
                    'display' => date('d M Y, H:i', strtotime($row->waktu_pesan)),
                    'timestamp' => $row->waktu_pesan
                ];
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'menunggu' => 'bg-label-warning',
                    'diproses' => 'bg-label-primary',
                    'siap' => 'bg-label-info',
                    'diantar' => 'bg-label-success',
                    'selesai' => 'bg-label-success',
                    'dibatalkan' => 'bg-label-danger'
                ];
                return '<span class="badge ' . $badges[$row->status] . '">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('payment_status_badge', function ($row) {
                $badges = [
                    'menunggu_pembayaran' => 'bg-label-warning',
                    'lunas' => 'bg-label-success'
                ];
                $texts = [
                    'menunggu_pembayaran' => 'Menunggu Pembayaran',
                    'lunas' => 'Lunas'
                ];
                return '<span class="badge ' . $badges[$row->status_pembayaran] . '">' . $texts[$row->status_pembayaran] . '</span>';
            })
            ->addColumn('payment_badge', function ($row) {
                $badges = [
                    'tunai' => 'text-success',
                    'kartu_kredit' => 'text-primary',
                    'kartu_debit' => 'text-info',
                    'e-wallet' => 'text-warning'
                ];
                return '<h6 class="mb-0 ' . $badges[$row->metode_pembayaran] . '">
                <i class="ri-circle-fill ri-10px me-1"></i>'
                    . ucfirst(str_replace('_', ' ', $row->metode_pembayaran)) .
                    '</h6>';
            })
            ->rawColumns(['status_badge', 'payment_status_badge', 'payment_badge'])
            ->make(true);
    }

    public function show($id)
    {
        $pesanan = Pesanan::with(['meja', 'kasir', 'details.menu'])
            ->findOrFail($id);
        return view('pages.kasir.pesanan.show', compact('pesanan'));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $pesanan = Pesanan::findOrFail($id);
            PesananDetail::where('id_pesanan', $id)->delete();

            DB::table('tbl_pesanan_antrian')
                ->where('id_pesanan', $id)
                ->delete();
            $pesanan->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createFromQrCode(Request $request, Meja $meja)
    {
        // Validasi token
        $token = $request->query('token');
        if ($meja->qr_code_token !== $token) {
            return abort(403, 'QR Code tidak valid');
        }

        // Validasi status meja
        if (!$meja->tersedia) {
            return view('pages.kasir.pesanan.qrcode_pesanan_error', [
                'meja' => $meja,
                'message' => 'Meja sedang tidak tersedia'
            ]);
        }

        // Ambil data menu untuk pemesanan
        $kategoriMenu = MenuKategori::with([
            'menus' => function ($query) {
                $query->where('tersedia', true);
            }
        ])->get();

        return view('pages.kasir.pesanan.qrcode_pesanan', compact('meja', 'kategoriMenu'));
    }

    public function storeFromQrCode(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'id_meja' => 'required|exists:tbl_meja,id',
                'atas_nama' => 'required|string|max:255',
                'jumlah_pelanggan' => 'required|integer|min:1',
                'catatan' => 'nullable|string',
                'menu' => 'required|array'
            ]);

            // Cek token meja
            $meja = Meja::findOrFail($request->id_meja);
            $token = $request->query('token');
            if ($meja->qr_code_token !== $token) {
                return abort(403, 'Token tidak valid');
            }

            // Cek apakah meja tersedia
            if (!$meja->tersedia) {
                return redirect()->back()->with('error', 'Meja sedang tidak tersedia');
            }

            // Ambil data menu yang dipilih
            $menuItems = [];
            $totalHarga = 0;
            $pajak = 0;

            foreach ($request->menu as $menuId => $data) {
                if (isset($data['jumlah']) && $data['jumlah'] > 0) {
                    $menu = Menu::findOrFail($menuId);

                    // Hitung subtotal
                    $hargaSatuan = $menu->harga_jual;
                    $diskon = $menu->diskon ?? 0;
                    $hargaSetelahDiskon = $hargaSatuan - ($hargaSatuan * $diskon / 100);
                    $subtotal = $hargaSetelahDiskon * $data['jumlah'];

                    $menuItems[] = [
                        'id_menu' => $menuId,
                        'jumlah' => $data['jumlah'],
                        'harga_satuan' => $hargaSatuan,
                        'diskon' => $diskon,
                        'subtotal' => $subtotal,
                        'catatan_khusus' => $data['catatan'] ?? null
                    ];

                    $totalHarga += $subtotal;
                }
            }

            // Jika tidak ada menu yang dipilih
            if (empty($menuItems)) {
                return redirect()->back()->with('error', 'Pilih minimal satu menu');
            }

            $pajak = $totalHarga * 0.1;
            $totalHargaFinal = $totalHarga + $pajak;

            $pesanan = new Pesanan();
            $pesanan->id_meja = $request->id_meja;
            $pesanan->id_kasir = null;
            $pesanan->atas_nama = $request->atas_nama;
            $pesanan->jumlah_pelanggan = $request->jumlah_pelanggan;
            $pesanan->waktu_pesan = now();
            $pesanan->status = 'menunggu';
            $pesanan->total_harga = $totalHargaFinal;
            $pesanan->pajak = $pajak;
            $pesanan->catatan = $request->catatan;
            $pesanan->metode_pembayaran = 'qr_code';
            $pesanan->save();

            // Simpan detail pesanan
            foreach ($menuItems as $item) {
                $detail = new PesananDetail();
                $detail->id_pesanan = $pesanan->id;
                $detail->id_menu = $item['id_menu'];
                $detail->jumlah = $item['jumlah'];
                $detail->harga_satuan = $item['harga_satuan'];
                $detail->diskon = $item['diskon'];
                $detail->subtotal = $item['subtotal'];
                $detail->catatan_khusus = $item['catatan_khusus'];
                $detail->save();
            }
            $meja->tersedia = false;
            $meja->save();

            DB::commit();

            return view('pages.kasir.pesanan.qrcode_pesanan_success', [
                'pesanan' => $pesanan,
                'meja' => $meja
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error menyimpan pesanan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan pesanan. Silakan coba lagi.');
        }
    }

}
