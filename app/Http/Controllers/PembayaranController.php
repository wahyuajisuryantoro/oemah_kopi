<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use App\Models\Meja;
use App\Models\Menu;
use Midtrans\Config;
use App\Models\Pesanan;
use Midtrans\Notification;
use Illuminate\Http\Request;
use App\Models\PesananDetail;
use App\Models\PesananAntrian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PembayaranController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = env('MIDTRANS_IS_SANITIZED', true);
        Config::$is3ds = env('MIDTRANS_IS_3DS', true);
    }

    public function createPayment(Request $request)
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

            $meja = Meja::findOrFail($request->id_meja);
            $token = $request->query('token');

            if ($meja->qr_code_token !== $token) {
                return abort(403, 'Token tidak valid');
            }

            if (!$meja->tersedia) {
                return redirect()->back()->with('error', 'Meja sedang tidak tersedia');
            }

            $idKasir = null;

            $menuItems = [];
            $totalHarga = 0;

            foreach ($request->menu as $menuId => $data) {
                if (isset($data['jumlah']) && $data['jumlah'] > 0) {
                    $menu = Menu::findOrFail($menuId);

                    $hargaSatuan = $menu->harga_jual;
                    $diskon = $menu->diskon ?? 0;
                    $hargaSetelahDiskon = $hargaSatuan - ($hargaSatuan * $diskon / 100);
                    $subtotal = $hargaSetelahDiskon * $data['jumlah'];

                    $menuItems[] = [
                        'id_menu' => $menuId,
                        'nama_menu' => $menu->nama_menu,
                        'jumlah' => $data['jumlah'],
                        'harga_satuan' => $hargaSatuan,
                        'diskon' => $diskon,
                        'subtotal' => $subtotal,
                        'catatan_khusus' => $data['catatan'] ?? null
                    ];

                    $totalHarga += $subtotal;
                }
            }

            if (empty($menuItems)) {
                return redirect()->back()->with('error', 'Pilih minimal satu menu');
            }

            $pajak = $totalHarga * 0.1;
            $totalHargaFinal = $totalHarga + $pajak;

            // 1. BUAT PESANAN dengan status pembayaran menunggu
            $pesanan = new Pesanan();
            $pesanan->id_meja = $request->id_meja;
            $pesanan->id_kasir = $idKasir;
            $pesanan->atas_nama = $request->atas_nama;
            $pesanan->jumlah_pelanggan = $request->jumlah_pelanggan;
            $pesanan->waktu_pesan = now();
            $pesanan->status = 'menunggu';
            $pesanan->status_pembayaran = 'menunggu_pembayaran';
            $pesanan->total_harga = $totalHargaFinal;
            $pesanan->pajak = $pajak;
            $pesanan->catatan = $request->catatan;
            $pesanan->metode_pembayaran = 'e-wallet';
            $pesanan->save();

            // 2. JANGAN BUAT ANTRIAN DULU - tunggu pembayaran sukses

            // 3. BUAT DETAIL PESANAN
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

            // 4. PROSES MIDTRANS
            $orderId = 'ORDER-' . $pesanan->id . '-' . time();

            $itemDetails = [];
            foreach ($menuItems as $item) {
                $itemDetails[] = [
                    'id' => $item['id_menu'],
                    'price' => (int) ($item['harga_satuan'] - ($item['harga_satuan'] * $item['diskon'] / 100)),
                    'quantity' => $item['jumlah'],
                    'name' => $item['nama_menu']
                ];
            }

            if ($pajak > 0) {
                $itemDetails[] = [
                    'id' => 'pajak',
                    'price' => (int) $pajak,
                    'quantity' => 1,
                    'name' => 'Pajak (10%)'
                ];
            }

            $transaction = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $totalHargaFinal
                ],
                'customer_details' => [
                    'first_name' => $request->atas_nama,
                    'email' => 'customer@oemahkopi.com',
                    'phone' => '081234567890'
                ],
                'item_details' => $itemDetails,
                'custom_field1' => "Meja: {$meja->nomor_meja}",
                'custom_field2' => $idKasir ? "Kasir ID: $idKasir" : 'Customer Self-Order'
            ];

            $snapToken = Snap::getSnapToken($transaction);

            $pesanan->update([
                'order_id' => $orderId,
                'snap_token' => $snapToken
            ]);

            DB::commit();
            return view('pages.kasir.pembayaran.qr_code_payment', [
                'pesanan' => $pesanan,
                'meja' => $meja,
                'snapToken' => $snapToken,
                'menuItems' => $menuItems
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating payment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pembayaran.');
        }
    }

    public function updatePaymentStatus(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|string',
                'transaction_status' => 'required|string',
                'payment_type' => 'nullable|string'
            ]);

            $orderId = $request->order_id;
            $transactionStatus = $request->transaction_status;

            Log::info('Client-side payment update received', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'payment_type' => $request->payment_type
            ]);

            $pesanan = Pesanan::where('order_id', $orderId)->first();

            if (!$pesanan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Update berdasarkan status dari client
            switch ($transactionStatus) {
                case 'capture':
                case 'settlement':
                    // UPDATE STATUS PEMBAYARAN MENJADI LUNAS
                    $pesanan->update([
                        'status' => 'diproses',
                        'status_pembayaran' => 'lunas'
                    ]);

                    // BUAT ANTRIAN DAPUR
                    $this->createPesananAntrianFromClient($pesanan);

                    // MEJA TIDAK TERSEDIA
                    $pesanan->meja->update(['tersedia' => false]);

                    Log::info('Client payment processed successfully', [
                        'pesanan_id' => $pesanan->id,
                        'order_id' => $orderId
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Payment status updated successfully',
                        'redirect_url' => route('payment.success', ['order_id' => $orderId])
                    ]);

                case 'pending':
                    $pesanan->update([
                        'status' => 'menunggu',
                        'status_pembayaran' => 'menunggu_pembayaran'
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Payment is pending',
                        'redirect_url' => route('payment.pending', ['order_id' => $orderId])
                    ]);

                case 'deny':
                case 'cancel':
                case 'expire':
                    $pesanan->update([
                        'status' => 'dibatalkan',
                        'status_pembayaran' => 'menunggu_pembayaran'
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Payment failed',
                        'redirect_url' => route('payment.error', ['order_id' => $orderId])
                    ]);

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unknown transaction status'
                    ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error updating payment status from client', [
                'message' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    private function createPesananAntrianFromClient(Pesanan $pesanan)
    {
        try {
            // Cek apakah sudah ada antrian
            $existingAntrian = PesananAntrian::where('id_pesanan', $pesanan->id)->first();
            if ($existingAntrian) {
                Log::info('Antrian already exists', ['pesanan_id' => $pesanan->id]);
                return;
            }

            // Generate nomor antrian
            $lastAntrian = PesananAntrian::whereDate('created_at', today())
                ->orderBy('nomor_antrian', 'desc')
                ->first();
            $nomorAntrian = $lastAntrian ? $lastAntrian->nomor_antrian + 1 : 1;

            $pesananAntrian = new PesananAntrian();
            $pesananAntrian->id_pesanan = $pesanan->id;
            $pesananAntrian->nomor_antrian = $nomorAntrian;
            $pesananAntrian->status_antrian = 'menunggu';
            $pesananAntrian->waktu_masuk_antrian = now();
            $pesananAntrian->save();

            Log::info('Antrian created from client', [
                'pesanan_id' => $pesanan->id,
                'nomor_antrian' => $nomorAntrian
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating antrian from client', [
                'pesanan_id' => $pesanan->id,
                'error' => $e->getMessage()
            ]);
        }
    }


    public function handleNotification(Request $request)
    {
        try {
            // Log semua yang masuk
            Log::info('=== MIDTRANS WEBHOOK RECEIVED ===', [
                'timestamp' => now(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $request->headers->all(),
                'raw_body' => $request->getContent(),
                'parsed_data' => $request->all()
            ]);

            // Coba buat notification object
            try {
                $notification = new Notification();
                Log::info('Notification object created successfully');
            } catch (\Exception $e) {
                Log::error('Failed to create Notification object', [
                    'error' => $e->getMessage(),
                    'server_key_set' => !empty(Config::$serverKey)
                ]);
                throw $e;
            }

            $transactionStatus = $notification->transaction_status;
            $orderId = $notification->order_id;
            $fraudStatus = $notification->fraud_status ?? null;

            Log::info('=== NOTIFICATION DATA ===', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'gross_amount' => $notification->gross_amount ?? null,
                'payment_type' => $notification->payment_type ?? null
            ]);

            $pesanan = Pesanan::where('order_id', $orderId)->first();

            if (!$pesanan) {
                Log::error('=== ORDER NOT FOUND ===', [
                    'searched_order_id' => $orderId,
                    'existing_orders' => Pesanan::select('id', 'order_id')->limit(5)->get()
                ]);
                return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
            }

            Log::info('=== ORDER FOUND ===', [
                'pesanan_id' => $pesanan->id,
                'current_status' => $pesanan->status,
                'current_payment_status' => $pesanan->status_pembayaran,
                'total_harga' => $pesanan->total_harga
            ]);

            switch ($transactionStatus) {
                case 'capture':
                    Log::info('Processing CAPTURE status', ['fraud_status' => $fraudStatus]);

                    if ($fraudStatus == 'challenge') {
                        $pesanan->update([
                            'status' => 'menunggu',
                            'status_pembayaran' => 'menunggu_pembayaran'
                        ]);
                        Log::info('Status updated to challenge');
                    } else if ($fraudStatus == 'accept') {
                        $this->processPaymentSuccess($pesanan, $orderId);
                    }
                    break;

                case 'settlement':
                    Log::info('Processing SETTLEMENT status');
                    $this->processPaymentSuccess($pesanan, $orderId);
                    break;

                case 'pending':
                    Log::info('Processing PENDING status');
                    $pesanan->update([
                        'status' => 'menunggu',
                        'status_pembayaran' => 'menunggu_pembayaran'
                    ]);
                    break;

                case 'deny':
                case 'expire':
                case 'cancel':
                    Log::info('Processing FAILED status', ['status' => $transactionStatus]);
                    $pesanan->update([
                        'status' => 'dibatalkan',
                        'status_pembayaran' => 'menunggu_pembayaran'
                    ]);
                    break;

                default:
                    Log::warning('Unknown transaction status', ['status' => $transactionStatus]);
            }

            Log::info('=== WEBHOOK PROCESSED SUCCESSFULLY ===');
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('=== WEBHOOK ERROR ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function processPaymentSuccess(Pesanan $pesanan, string $orderId)
    {
        try {
            Log::info('=== PROCESSING PAYMENT SUCCESS ===', [
                'pesanan_id' => $pesanan->id,
                'order_id' => $orderId
            ]);

            // Update status pembayaran menjadi lunas
            $pesanan->update([
                'status' => 'diproses',
                'status_pembayaran' => 'lunas'
            ]);
            Log::info('Payment status updated to lunas');

            // Buat antrian dapur
            $existingAntrian = PesananAntrian::where('id_pesanan', $pesanan->id)->first();
            if (!$existingAntrian) {
                $lastAntrian = PesananAntrian::whereDate('created_at', today())
                    ->orderBy('nomor_antrian', 'desc')
                    ->first();
                $nomorAntrian = $lastAntrian ? $lastAntrian->nomor_antrian + 1 : 1;

                $pesananAntrian = new PesananAntrian();
                $pesananAntrian->id_pesanan = $pesanan->id;
                $pesananAntrian->nomor_antrian = $nomorAntrian;
                $pesananAntrian->status_antrian = 'menunggu';
                $pesananAntrian->waktu_masuk_antrian = now();
                $pesananAntrian->save();

                Log::info('Queue created successfully', ['queue_number' => $nomorAntrian]);
            } else {
                Log::info('Queue already exists', ['existing_queue' => $existingAntrian->nomor_antrian]);
            }

            // Meja tidak tersedia
            $pesanan->meja->update(['tersedia' => false]);
            Log::info('Table marked as unavailable');

            Log::info('=== PAYMENT SUCCESS PROCESSED COMPLETELY ===');

        } catch (\Exception $e) {
            Log::error('=== ERROR IN PAYMENT SUCCESS PROCESSING ===', [
                'pesanan_id' => $pesanan->id,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    public function paymentSuccess(Request $request)
    {
        $orderId = $request->query('order_id');
        $pesanan = Pesanan::where('order_id', $orderId)->with(['meja', 'details.menu'])->first();

        if (!$pesanan) {
            return redirect()->route('home')->with('error', 'Pesanan tidak ditemukan');
        }

        return view('pages.kasir.pembayaran.qr_code_payment_success', compact('pesanan'));
    }

    public function paymentPending(Request $request)
    {
        $orderId = $request->query('order_id');
        $pesanan = Pesanan::where('order_id', $orderId)->with(['meja', 'details.menu'])->first();

        return view('pages.kasir.pembayaran.qr_code_payment_pending', compact('pesanan'));
    }

    public function paymentError(Request $request)
    {
        $orderId = $request->query('order_id');
        $pesanan = Pesanan::where('order_id', $orderId)->with(['meja', 'details.menu'])->first();

        return view('pages.kasir.pembayaran.qr_code_payment_error', [
            'pesanan' => $pesanan,
            'error_message' => $request->query('error_message', 'Terjadi kesalahan')
        ]);
    }

    private function createPesananAntrian(Pesanan $pesanan)
    {
        try {
            $nomorAntrian = $this->generateNomorAntrian();

            $pesananAntrian = new PesananAntrian();
            $pesananAntrian->id_pesanan = $pesanan->id;
            $pesananAntrian->nomor_antrian = $nomorAntrian;
            $pesananAntrian->status_antrian = 'menunggu';
            $pesananAntrian->waktu_masuk_antrian = now();
            $pesananAntrian->save();
        } catch (\Exception $e) {
            Log::error('Error creating pesanan antrian: ' . $e->getMessage());
        }
    }

    private function generateNomorAntrian()
    {
        $lastAntrian = PesananAntrian::whereDate('created_at', today())
            ->orderBy('nomor_antrian', 'desc')
            ->first();

        if ($lastAntrian) {
            return $lastAntrian->nomor_antrian + 1;
        }
        return 1;
    }
}
