<?php

namespace App\Http\Controllers;

use App\Models\Meja;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Kasir_MejaController extends Controller
{
    public function index()
    {
        $title = "Manajemen Meja";
        $mejas = Meja::all();
        return view('pages.kasir.meja.index', compact('title', 'mejas'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nomor_meja' => 'required|unique:tbl_meja',
                'kapasitas' => 'required|integer|min:1',
                'lokasi' => 'nullable|string',
                'tersedia' => 'boolean'
            ]);

            $data = $request->all();
            $data['tersedia'] = $request->has('tersedia');
            $data['qr_code_token'] = Str::random(32);

            Log::info('Attempting to create new meja with data:', $data);

            $meja = Meja::create($data);

            Log::info('Meja created successfully:', ['id' => $meja->id]);

            Alert::success('Berhasil', 'Meja berhasil ditambahkan dengan QR code');
            return redirect()->route('meja.index');
        } catch (\Exception $e) {
            Log::error('Error creating meja:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Alert::error('Error', 'Terjadi kesalahan saat menambahkan meja');
            return redirect()->back()->withInput();
        }
    }

    public function update(Request $request, Meja $meja)
    {
        try {
            $request->validate([
                'nomor_meja' => 'required|unique:tbl_meja,nomor_meja,' . $meja->id,
                'kapasitas' => 'required|integer|min:1',
                'lokasi' => 'nullable|string'
            ]);

            $oldNomorMeja = $meja->nomor_meja;
            $meja->update($request->all());
            if ($oldNomorMeja != $meja->nomor_meja && $meja->qr_code_path) {
                if (file_exists(public_path($meja->qr_code_path))) {
                    unlink(public_path($meja->qr_code_path));
                }
                $meja->qr_code_path = null;
                $meja->save();
            }

            Alert::success('Berhasil', 'Meja berhasil diperbarui');
            return redirect()->route('meja.index');
        } catch (\Exception $e) {
            Log::error('Error updating meja:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Alert::error('Error', 'Terjadi kesalahan saat memperbarui meja');
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Meja $meja)
    {
        if (!$meja->tersedia) {
            return response()->json(['message' => 'Meja sedang terisi dan tidak dapat dihapus.'], 403);
        }

        try {
            if ($meja->qr_code_path && file_exists(public_path($meja->qr_code_path))) {
                unlink(public_path($meja->qr_code_path));
            }

            $meja->delete();
            return response()->json(['message' => 'Meja berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus meja.'], 500);
        }
    }

    public function showQrCode(Meja $meja)
    {
        if (!$meja->qr_code_token) {
            $meja->qr_code_token = Str::random(32);
            $meja->save();

            Log::info('QR code token dibuat otomatis untuk meja:', ['id' => $meja->id, 'nomor_meja' => $meja->nomor_meja]);
        }
        $title = "QR Code Meja " . $meja->nomor_meja;
        $orderUrl = url('/pemesanan/meja/' . $meja->id . '?token=' . $meja->qr_code_token);
        $qrCodeSvg = QrCode::size(300)->errorCorrection('H')->generate($orderUrl);
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'qrCodeSvg' => $qrCodeSvg,
                'nomor_meja' => $meja->nomor_meja,
                'regenerateUrl' => route('meja.regenerate-qrcode', $meja),
                'orderPageUrl' => $orderUrl
            ]);
        }

        return view('pages.kasir.meja.qrcode', compact('title', 'meja', 'qrCodeSvg', 'orderUrl'));
    }

    /**
     * Generate QR code untuk meja
     */
    public function generateQrCode(Meja $meja)
    {
        try {
            if (!$meja->qr_code_token) {
                $meja->qr_code_token = Str::random(32);
                $meja->save();
            }

            $folderPath = public_path('qrcodes');
            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0775, true);
            }

            $fileName = 'meja_' . $meja->nomor_meja . '_qr.svg';
            $filePath = 'qrcodes/' . $fileName;
            $fullPath = public_path($filePath);

            $orderUrl = url('/pemesanan/meja/' . $meja->id . '?token=' . $meja->qr_code_token);

            $qrSvg = QrCode::size(300)
                ->style('round')
                ->eye('circle')
                ->errorCorrection('H') 
                ->margin(2) 
                ->generate($orderUrl);

            file_put_contents($fullPath, $qrSvg);

            // Update path QR code di database
            $meja->qr_code_path = $filePath;
            $meja->save();

            Log::info('QR code berhasil dibuat untuk meja:', [
                'id' => $meja->id,
                'nomor_meja' => $meja->nomor_meja,
                'file_path' => $filePath
            ]);

            Alert::success('Berhasil', 'QR code berhasil disimpan');
            return redirect()->route('meja.show-qrcode', $meja);
        } catch (\Exception $e) {
            Log::error('Error saat membuat QR code:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'meja_id' => $meja->id
            ]);

            Alert::error('Error', 'Terjadi kesalahan saat membuat QR code: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Regenerasi QR code meja
     */
    public function regenerateQrCode(Meja $meja)
    {
        try {
            // Hapus QR code lama jika ada
            if ($meja->qr_code_path && file_exists(public_path($meja->qr_code_path))) {
                unlink(public_path($meja->qr_code_path));
            }

            // Generate token baru
            $meja->qr_code_token = Str::random(32);
            $meja->qr_code_path = null;
            $meja->save();

            Log::info('Token QR code diregenerasi untuk meja:', ['id' => $meja->id, 'nomor_meja' => $meja->nomor_meja]);

            // Jika request AJAX, kembalikan data JSON
            if (request()->ajax()) {
                $orderUrl = url('/pemesanan/meja/' . $meja->id . '?token=' . $meja->qr_code_token);
                $qrCodeSvg = QrCode::size(300)->errorCorrection('H')->generate($orderUrl);

                return response()->json([
                    'success' => true,
                    'qrCodeSvg' => $qrCodeSvg,
                    'orderPageUrl' => $orderUrl
                ]);
            }

            Alert::success('Berhasil', 'QR code berhasil diregenerasi');
            return redirect()->route('meja.show-qrcode', $meja);
        } catch (\Exception $e) {
            Log::error('Error saat meregenerasi QR code:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat meregenerasi QR code: ' . $e->getMessage()
                ], 500);
            }

            Alert::error('Error', 'Terjadi kesalahan saat meregenerasi QR code');
            return redirect()->back();
        }
    }

    public function toggleStatus(Meja $meja)
    {
        $meja->tersedia = !$meja->tersedia;
        $meja->save();

        $status = $meja->tersedia ? 'tersedia' : 'tidak tersedia';
        Alert::success('Berhasil', "Status meja berhasil diubah menjadi $status");
        return redirect()->route('meja.index');
    }
}