<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\PesananAntrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;

class Dapur_DashboardController extends Controller
{
    public function index()
    {
        try {
            $title = "Dashboard Kitchen";
            $antrian_pesanan = PesananAntrian::with([
                'pesanan' => function ($query) {
                    $query->with(['meja', 'details.menu', 'kasir'])
                        ->where('status_pembayaran', 'lunas')
                        ->whereIn('status', ['menunggu', 'diproses']);
                }
            ])
                ->whereIn('status_antrian', ['menunggu', 'diproses'])
                ->orderBy('nomor_antrian', 'asc')
                ->get()
                ->filter(function ($antrian) {
                    return $antrian->pesanan !== null && $antrian->pesanan->status_pembayaran === 'lunas';
                });

            Log::info('Halaman dapur diakses', [
                'user_id' => Auth::id(),
                'total_antrian' => $antrian_pesanan->count()
            ]);

            $stats = [
                'menunggu' => $antrian_pesanan->where('status_antrian', 'menunggu')->count(),
                'diproses' => $antrian_pesanan->where('status_antrian', 'diproses')->count(),
                'selesai' => PesananAntrian::whereDate('created_at', today())
                    ->where('status_antrian', 'selesai')
                    ->whereHas('pesanan', function ($query) {
                        $query->where('status_pembayaran', 'lunas');
                    })
                    ->count()
            ];

            return view('pages.dapur.dashboard', compact('title', 'antrian_pesanan', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error pada halaman dapur', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Alert::error('Error', 'Terjadi kesalahan saat memuat halaman dapur');
            return redirect()->back();
        }
    }
    public function updateStatus(Request $request, Pesanan $pesanan)
    {
        try {
            $request->validate([
                'status' => 'required|in:diproses,selesai'
            ]);

            $status = $request->status;

            // Update status pesanan
            $pesanan->status = $status;
            $pesanan->save();

            // Update status antrian
            if ($pesanan->antrian) {
                $pesanan->antrian->status_antrian = $status;
                if ($status === 'selesai') {
                    $pesanan->antrian->waktu_keluar_antrian = now();
                }
                $pesanan->antrian->save();

                // Jika pesanan selesai, update status meja
                if ($status === 'selesai') {
                    $pesanan->meja->tersedia = true;
                    $pesanan->meja->save();
                }
            }

            Log::info('Status pesanan diupdate', [
                'user_id' => Auth::id(),
                'pesanan_id' => $pesanan->id,
                'status_baru' => $status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status pesanan berhasil diupdate',
                'data' => $pesanan->load('antrian')
            ]);

        } catch (\Exception $e) {
            Log::error('Error saat update status pesanan', [
                'user_id' => Auth::id(),
                'pesanan_id' => $pesanan->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAntrianData()
    {
        try {
            $antrian_pesanan = PesananAntrian::with([
                'pesanan' => function ($query) {
                    $query->with(['meja', 'detail.menu'])
                        ->whereIn('status', ['menunggu', 'diproses']);
                }
            ])
                ->whereIn('status_antrian', ['menunggu', 'diproses'])
                ->orderBy('nomor_antrian', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $antrian_pesanan
            ]);

        } catch (\Exception $e) {
            Log::error('Error saat mengambil data antrian', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data antrian'
            ], 500);
        }
    }
}
