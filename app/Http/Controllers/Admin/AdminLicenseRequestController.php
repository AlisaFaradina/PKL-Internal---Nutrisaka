<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseRequest;
use App\Models\LicenseToken;
use App\Services\LicenseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AdminLicenseRequestController extends Controller
{
    public function __construct(
        protected LicenseService $licenseService
    ) {}

    /**
     * Menampilkan daftar permohonan lisensi dari calon pengguna.
     */
    public function index(Request $request)
    {
        $query = LicenseRequest::query()->latest();

        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('device_id', 'LIKE', "%{$search}%");
            });
        }

        $requests = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => LicenseRequest::count(),
            'pending' => LicenseRequest::where('status', 'pending')->count(),
            'approved' => LicenseRequest::where('status', 'approved')->count(),
            'rejected' => LicenseRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.requests.index', compact('requests', 'stats'));
    }

    /**
     * Menampilkan detail permohonan lisensi.
     */
    public function show(LicenseRequest $licenseRequest)
    {
        return view('admin.requests.show', compact('licenseRequest'));
    }

    /**
     * Menerbitkan (Generate) Lisensi untuk Device ID pemohon.
     */
    public function generate(Request $request, LicenseRequest $licenseRequest)
    {
        if ($licenseRequest->isApproved() && !empty($licenseRequest->license_token)) {
            return back()->with('info', "Permohonan ini sudah disetujui sebelumnya dengan token: {$licenseRequest->license_token}");
        }

        // Generate Windows 25-character Product Key
        $productKey = LicenseService::generateWindowsProductKey();

        // Buat record token resmi di pool tokens
        $tokenRecord = LicenseToken::create([
            'token' => $productKey,
            'status' => 'unused',
            'supplier_name' => $licenseRequest->business_name,
            'contact' => $licenseRequest->phone,
            'notes' => "Diterbitkan untuk permohonan #{$licenseRequest->id} (Device ID: {$licenseRequest->device_id})",
        ]);

        $adminId = auth()->guard('admin')->id();

        // Update status permohonan
        $licenseRequest->update([
            'status' => 'approved',
            'license_token' => $productKey,
            'processed_at' => now(),
            'processed_by' => $adminId,
            'notes' => $request->input('notes', 'Lisensi resmi telah diterbitkan oleh Administrator.'),
        ]);

        // Kirim email notifikasi lisensi ke pengguna
        $emailSent = false;
        try {
            $userEmail = $licenseRequest->email;
            $subject = "[Nutrisaka] Lisensi Resmi Perangkat Anda Siap Digunakan";
            $body = "Halo {$licenseRequest->business_name},\n\n"
                  . "Permohonan lisensi perangkat Nutrisaka Anda telah disetujui oleh Administrator.\n\n"
                  . "DETAIL LISENSI PERANGKAT ANDA:\n"
                  . "- Nama Usaha   : {$licenseRequest->business_name}\n"
                  . "- Device ID    : {$licenseRequest->device_id}\n"
                  . "- Kode Lisensi : {$productKey}\n"
                  . "- Masa Berlaku : Aktif Selamanya (Lifetime)\n\n"
                  . "PETUNJUK AKTIVASI:\n"
                  . "1. Buka aplikasi Nutrisaka pada perangkat yang Anda daftarkan.\n"
                  . "2. Masuk ke halaman 'Aktivasi Lisensi'.\n"
                  . "3. Masukkan Kode Lisensi di atas.\n"
                  . "4. Tekan tombol 'Aktivasi & Mulai Gunakan Aplikasi'.\n\n"
                  . "Terima kasih telah mempercayakan operasional pasokan SPPG Anda kepada Nutrisaka.\n\n"
                  . "Salam Hangat,\nTim Nutrisaka Support (sakanutri@gmail.com)";

            Mail::raw($body, function ($msg) use ($userEmail, $subject) {
                $msg->to($userEmail)->subject($subject);
            });
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email lisensi ke {$licenseRequest->email}: " . $e->getMessage());
        }

        // Siapkan tautan WhatsApp jika nomor tersedia
        $cleanPhone = preg_replace('/[^0-9]/', '', $licenseRequest->phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }
        $waText = urlencode("Halo {$licenseRequest->business_name}, lisensi resmi Nutrisaka untuk perangkat Device ID: {$licenseRequest->device_id} telah aktif! Kode Lisensi Anda: {$productKey}. Masukkan kode ini di halaman aktivasi aplikasi Nutrisaka.");
        $waLink = "https://wa.me/{$cleanPhone}?text={$waText}";

        $message = "Lisensi baru {$productKey} berhasil diterbitkan untuk {$licenseRequest->business_name}!";
        if ($emailSent) {
            $message .= " Email berisi kode lisensi telah dikirimkan ke {$licenseRequest->email}.";
        } else {
            $message .= " (Catatan: Pengiriman email otomatis terkendala, silakan salin token atau kirim manual via WhatsApp).";
        }

        return redirect()->route('admin.requests.show', $licenseRequest)
            ->with('success', $message)
            ->with('generated_key', $productKey)
            ->with('wa_link', $waLink);
    }

    /**
     * Menolak permohonan lisensi.
     */
    public function reject(Request $request, LicenseRequest $licenseRequest)
    {
        $licenseRequest->update([
            'status' => 'rejected',
            'processed_at' => now(),
            'processed_by' => auth()->guard('admin')->id(),
            'notes' => $request->input('notes', 'Permohonan ditolak oleh Administrator.'),
        ]);

        return back()->with('info', "Permohonan lisensi dari {$licenseRequest->business_name} telah ditolak.");
    }
}
