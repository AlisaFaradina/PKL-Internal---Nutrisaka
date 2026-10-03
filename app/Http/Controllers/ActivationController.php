<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\License;
use App\Models\LicenseRequest;
use App\Models\User;
use App\Services\LicenseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ActivationController extends Controller
{
    public function __construct(
        protected LicenseService $licenseService
    ) {}

    /**
     * Menampilkan Halaman Onboarding / Pemilihan Mode Aplikasi.
     */
    public function showOnboarding()
    {
        $verification = $this->licenseService->verifyWithTrial();
        $deviceId = $this->licenseService->getDeviceId();
        $shortId = $this->licenseService->getShortFingerprint();
        $appId = LicenseService::APPLICATION_ID;
        $currentMode = AppSetting::getApplicationMode();
        $settings = AppSetting::getAllSettings();
        $trialStatus = $this->licenseService->getTrialStatus();

        return view('activation.onboarding', compact(
            'verification',
            'deviceId',
            'shortId',
            'appId',
            'currentMode',
            'settings',
            'trialStatus'
        ));
    }

    /**
     * Memulai aplikasi dalam Mode Demo (Simulasi Data).
     */
    public function startDemoMode(Request $request)
    {
        AppSetting::setApplicationMode('demo');

        // Pastikan ada data simulasi jika tabel masih kosong
        $hasSampleData = \App\Models\Product::withoutGlobalScopes()
            ->where('is_simulation', true)
            ->exists();

        if (!$hasSampleData) {
            try {
                $seeder = new \Database\Seeders\NutrisakaSampleSeeder();
                $seeder->run();
            } catch (\Throwable $e) {
                Log::warning('Auto-seeding demo mode failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('dashboard')
            ->with('success', 'Mode Demo Aktif: Anda sedang mencoba Nutrisaka dengan data contoh/simulasi. Data operasional nyata Anda tetap bersih dan terlindungi.');
    }

    /**
     * Memilih Mode Asli (Operasional).
     */
    public function startRealMode(Request $request)
    {
        AppSetting::setApplicationMode('real');

        $verification = $this->licenseService->verifyWithTrial();

        if ($verification['valid']) {
            return redirect()->route('dashboard')
                ->with('success', 'Mode Asli Aktif: Berhasil beralih ke data operasional.');
        }

        return redirect()->route('onboarding')
            ->with('info', 'Mode Asli Aktif: Silakan mulai Trial 7 Hari atau daftarkan perangkat Anda untuk mendapatkan lisensi resmi.');
    }

    /**
     * API Endpoint untuk mengambil Device ID aktual dari perangkat keras.
     */
    public function getDeviceIdApi()
    {
        $deviceId = $this->licenseService->getDeviceId();
        $shortId = $this->licenseService->getShortFingerprint();
        $appId = LicenseService::APPLICATION_ID;
        $details = $this->licenseService->getDeviceDetails();

        return response()->json([
            'success' => true,
            'device_id' => $deviceId,
            'short_device_id' => $shortId,
            'app_id' => $appId,
            'device_details' => $details,
        ]);
    }

    /**
     * Menerima pendaftaran user/usaha dan membuat permohonan lisensi resmi ke admin.
     */
    public function submitLicenseRequest(Request $request)
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:4', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'email' => ['required', 'email', 'max:255'],
            'device_id' => ['required', 'string', 'max:100'],
        ], [
            'business_name.required' => 'Nama usaha / perusahaan supplier wajib diisi.',
            'password.required' => 'Password akun wajib dibuat.',
            'password.min' => 'Password minimal terdiri dari 4 karakter.',
            'phone.required' => 'Nomor WhatsApp / telepon aktif wajib diisi.',
            'email.required' => 'Alamat email aktif wajib diisi untuk penerimaan lisensi.',
            'email.email' => 'Format alamat email tidak valid.',
            'device_id.required' => 'Device ID wajib diambil dari perangkat Anda.',
        ]);

        $actualDeviceId = $this->licenseService->getDeviceId();
        $submittedDeviceId = trim($request->input('device_id'));

        // Pastikan Device ID terisi dengan valid
        if (empty($submittedDeviceId)) {
            $submittedDeviceId = $actualDeviceId;
        }

        // HASH PASSWORD — JANGAN PERNAH MENYIMPAN ATAU MENGIRIM PLAINTEXT!
        $hashedPassword = Hash::make($request->input('password'));

        // Simpan atau perbarui akun User lokal
        try {
            User::updateOrCreate(
                ['email' => $request->input('email')],
                [
                    'name' => $request->input('business_name'),
                    'password' => $hashedPassword,
                ]
            );
        } catch (\Throwable $e) {
            Log::info('User record update note: ' . $e->getMessage());
        }

        // Simpan data identitas profil usaha ke AppSetting
        AppSetting::set('supplier_name', $request->input('business_name'));
        AppSetting::set('supplier_phone', $request->input('phone'));
        AppSetting::set('supplier_email', $request->input('email'));
        if ($request->filled('address')) {
            AppSetting::set('supplier_address', $request->input('address'));
        }

        // Catat LicenseRequest ke database
        $licenseRequest = LicenseRequest::create([
            'business_name' => $request->input('business_name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address', ''),
            'device_id' => $submittedDeviceId,
            'app_id' => LicenseService::APPLICATION_ID,
            'password' => $hashedPassword,
            'status' => 'pending',
            'requested_at' => now(),
            'notes' => 'Permohonan lisensi baru dikirim dari aplikasi Nutrisaka.',
        ]);

        // Kirim email permohonan ke admin: sakanutri@gmail.com
        // TIDAK MENYERTAKAN PASSWORD USER!
        $adminEmail = 'sakanutri@gmail.com';
        $emailSubject = '[Nutrisaka] Permohonan Lisensi Perangkat Baru';
        $emailBody = "Permohonan Lisensi Nutrisaka\n\n"
            . "Nama Usaha: {$licenseRequest->business_name}\n"
            . "Email: {$licenseRequest->email}\n"
            . "Nomor: {$licenseRequest->phone}\n"
            . "Alamat: " . ($licenseRequest->address ?: '-') . "\n"
            . "Device ID: {$licenseRequest->device_id}\n"
            . "APP ID: {$licenseRequest->app_id}\n"
            . "Request ID: #{$licenseRequest->id}\n"
            . "Application Version: " . LicenseService::APPLICATION_VERSION . "\n"
            . "Tanggal Permohonan: " . $licenseRequest->requested_at->format('d F Y H:i') . " WIB\n"
            . "Status: Pending\n\n"
            . "Catatan: Segera buka Admin Panel Nutrisaka untuk memverifikasi dan menerbitkan lisensi perangkat ini.";

        $mailSent = false;
        try {
            Mail::raw($emailBody, function ($message) use ($adminEmail, $emailSubject) {
                $message->to($adminEmail)->subject($emailSubject);
            });
            $mailSent = true;
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email permohonan lisensi ke admin: ' . $e->getMessage());
        }

        if ($mailSent) {
            $msg = "Permohonan lisensi untuk '{$licenseRequest->business_name}' berhasil dikirim ke Admin ({$adminEmail}). Token lisensi resmi akan dikirim ke email Anda ({$licenseRequest->email}). Sambil menunggu, Anda dapat langsung mengklik 'Mulai Trial 7 Hari' di bawah.";
            return redirect()->route('onboarding')
                ->with('success', $msg)
                ->with('just_requested', true);
        }

        $warningMsg = "Permohonan lisensi berhasil disimpan di sistem (ID: #{$licenseRequest->id}). Pengiriman email otomatis mengalami kendala jaringan, namun Admin tetap dapat memproses lisensi Anda melalui Admin Panel.";
        return redirect()->route('onboarding')
            ->with('warning', $warningMsg)
            ->with('just_requested', true);
    }

    /**
     * Menampilkan Halaman Status Masa Uji Coba (Trial 7 Hari).
     */
    public function showTrialStatus()
    {
        $license = License::first();
        $trialStatus = $this->licenseService->getTrialStatus();
        $timeRemaining = $trialStatus['time_remaining'] ?? ['days' => 0, 'hours' => 0, 'formatted' => '-'];
        $deviceId = $this->licenseService->getDeviceId();

        return view('activation.trial_status', compact('license', 'trialStatus', 'timeRemaining', 'deviceId'));
    }

    /**
     * Menampilkan Halaman Trial Expired yang ramah awam tanpa menghapus data.
     */
    public function showTrialExpired()
    {
        $license = License::first();
        $trialStatus = $this->licenseService->getTrialStatus();
        $deviceId = $this->licenseService->getDeviceId();
        $shortId = $this->licenseService->getShortFingerprint();

        return view('activation.trial_expired', compact('license', 'trialStatus', 'deviceId', 'shortId'));
    }

    /**
     * Menampilkan Halaman Registrasi Token & Data Usaha Supplier.
     */
    public function showRegister()
    {
        $verification = $this->licenseService->verifyWithTrial();

        if ($verification['valid']) {
            return redirect()->route('dashboard')
                ->with('info', $verification['message']);
        }

        $fingerprint = $this->licenseService->getDeviceFingerprint();
        $shortFingerprint = $this->licenseService->getShortFingerprint();
        $deviceId = $this->licenseService->getDeviceId();
        $settings = AppSetting::getAllSettings();
        $isLocal = app()->environment('local', 'testing');

        return view('activation.register', compact(
            'fingerprint',
            'shortFingerprint',
            'deviceId',
            'settings',
            'isLocal'
        ));
    }

    /**
     * Generate mailto link untuk permintaan token lisensi.
     */
    public function requestLicenseToken(Request $request)
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $deviceId = $this->licenseService->getDeviceId();
        $appId = LicenseService::APPLICATION_ID;

        $to = 'sakanutri@gmail.com';
        $subject = 'Permintaan Token Lisensi Nutrisaka';
        $body = "Yth. Admin Nutrisaka,\n\n"
            . "Saya ingin mengajukan permintaan token lisensi Nutrisaka.\n\n"
            . "Nama Usaha: " . $request->input('business_name') . "\n"
            . "Nomor Telepon: " . $request->input('phone') . "\n"
            . "Email: " . $request->input('email') . "\n"
            . "Alamat: " . ($request->input('address') ?: '-') . "\n"
            . "Device ID: " . $deviceId . "\n"
            . "APP ID: " . $appId . "\n\n"
            . "Terima kasih.";

        $mailto = 'mailto:' . $to
            . '?subject=' . rawurlencode($subject)
            . '&body=' . rawurlencode($body);

        // Detect NativePHP vs browser
        $isNativePHP = class_exists('Native\Laravel\Facades\Window') || app()->environment('production');

        if ($isNativePHP) {
            // NativePHP: use Window::openExternal
            if (class_exists('Native\Laravel\Facades\Window')) {
                \Native\Laravel\Facades\Window::openExternal($mailto);
            }
            return back()->with('success', 'Email client terbuka. Silakan kirim email permohonan token lisensi.');
        } else {
            // Browser: redirect
            return redirect()->away($mailto);
        }
    }

    /**
     * Generate draft text untuk copy-paste permintaan token.
     */
    public function copyLicenseRequestDraft(Request $request)
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $deviceId = $this->licenseService->getDeviceId();
        $appId = LicenseService::APPLICATION_ID;

        $draft = "Yth. Admin Nutrisaka,\n\n"
            . "Saya ingin mengajukan permintaan token lisensi Nutrisaka.\n\n"
            . "Nama Usaha: " . $request->input('business_name') . "\n"
            . "Nomor Telepon: " . $request->input('phone') . "\n"
            . "Email: " . $request->input('email') . "\n"
            . "Alamat: " . ($request->input('address') ?: '-') . "\n"
            . "Device ID: " . $deviceId . "\n"
            . "APP ID: " . $appId . "\n\n"
            . "Terima kasih.";

        return response()->json([
            'success' => true,
            'draft' => $draft,
        ]);
    }

    /**
     * Memproses pendaftaran token dan inisialisasi profil usaha supplier.
     * Token lisensi OPSIONAL - jika kosong, mulai Free Trial 7 hari.
     */
    public function register(Request $request)
    {
        $request->validate([
            'token' => ['nullable', 'string', 'max:50'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'pic_name' => ['required', 'string', 'max:255'],
            'supplier_phone' => ['required', 'string', 'max:50'],
            'supplier_address' => ['nullable', 'string', 'max:500'],
            'email' => ['required', 'email', 'max:255'],
            'pin' => ['required', 'string', 'min:4', 'max:6'],
        ], [
            'supplier_name.required' => 'Nama usaha / perusahaan supplier wajib diisi.',
            'pic_name.required' => 'Nama penanggung jawab (PIC) wajib diisi.',
            'supplier_phone.required' => 'Nomor telepon / WhatsApp aktif wajib diisi.',
            'email.required' => 'Alamat email aktif wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'pin.required' => 'PIN keamanan lokal 4-6 angka wajib dibuat.',
            'pin.min' => 'PIN keamanan minimal terdiri dari 4 angka.',
            'pin.max' => 'PIN keamanan maksimal terdiri dari 6 angka.',
        ]);

        $token = $request->input('token');
        $supplierData = $request->only([
            'supplier_name',
            'pic_name',
            'supplier_phone',
            'supplier_address',
            'email',
            'pin',
        ]);

        try {
            // Jika token diisi, aktivasi dengan token
            if (!empty($token)) {
                $this->licenseService->activate($token, $supplierData);
                AppSetting::setApplicationMode('real');
                return redirect()->route('dashboard')
                    ->with('success', 'Selamat Datang di Nutrisaka! Registrasi lisensi perangkat berhasil diaktivasi dan data usaha Anda telah tersimpan.');
            }

            // Jika token kosong, mulai Free Trial 7 hari
            // Cek apakah perangkat ini sudah pernah trial
            if ($this->licenseService->hasDeviceTrial()) {
                return back()->withInput()->with('error', 'Perangkat ini sudah pernah menggunakan Free Trial. Silakan masukkan token lisensi untuk melanjutkan penggunaan.');
            }

            $this->licenseService->startTrial();

            // Simpan data supplier ke app_settings
            AppSetting::set('supplier_name', $supplierData['supplier_name']);
            AppSetting::set('supplier_pic', $supplierData['pic_name']);
            AppSetting::set('supplier_phone', $supplierData['supplier_phone']);
            AppSetting::set('supplier_email', $supplierData['email']);
            AppSetting::set('supplier_address', $supplierData['supplier_address'] ?? '');
            AppSetting::set('security_pin', $supplierData['pin']);
            AppSetting::setApplicationMode('real');

            return redirect()->route('dashboard')
                ->with('success', 'Selamat Datang di Nutrisaka! Free Trial 7 hari telah dimulai. Semua fitur dapat digunakan.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Registrasi instan menggunakan token demo untuk lingkungan lokal / demo.
     */
    public function registerDemo()
    {
        if (!app()->environment('local', 'testing')) {
            abort(403, 'Aksi demo hanya diizinkan pada lingkungan pengembangan.');
        }

        try {
            $demoToken = 'NTRS-DEMO-2026-DEV1';
            $this->licenseService->activate($demoToken, [
                'supplier_name' => 'CV Nutrisaka Pangan Mandiri (Demo)',
                'pic_name' => 'Ahmad Fauzi',
                'supplier_phone' => '0812-3456-7890',
                'supplier_address' => 'Kawasan Logistik Pergudangan SPPG No. 12, Jakarta',
                'pin' => '1234',
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Mode Demo Aktif: Lisensi pengembangan dan data usaha awal berhasil diaktivasi.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mengaktifkan token demo: ' . $e->getMessage());
        }
    }

    /**
     * Memulai masa uji coba gratis 7 hari (Free Trial).
     */
    public function startTrial(Request $request)
    {
        try {
            $this->licenseService->startTrial();

            // Setel mode ke Mode Asli (Trial dijalankan pada data operasional asli pengguna)
            AppSetting::setApplicationMode('real');

            if ($request->filled('supplier_name')) {
                AppSetting::set('supplier_name', $request->input('supplier_name'));
            }
            if ($request->filled('pic_name')) {
                AppSetting::set('supplier_pic', $request->input('pic_name'));
            }
            if ($request->filled('supplier_phone')) {
                AppSetting::set('supplier_phone', $request->input('supplier_phone'));
            }
            if ($request->filled('pin')) {
                AppSetting::setPin($request->input('pin'));
            }

            return redirect()->route('dashboard')
                ->with('success', 'Masa Uji Coba Gratis (Trial) 7 Hari telah aktif! Anda dapat menggunakan fitur Dashboard, Pesanan, dan Penjualan.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memulai masa uji coba: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan layar terkunci ramah awam jika lisensi berakhir atau dicabut.
     */
    public function showLocked()
    {
        $license = License::first();
        $fingerprint = $this->licenseService->getDeviceFingerprint();
        $shortFingerprint = $this->licenseService->getShortFingerprint();
        $deviceId = $this->licenseService->getDeviceId();

        return view('activation.locked', compact('license', 'fingerprint', 'shortFingerprint', 'deviceId'));
    }

    /**
     * Validasi ulang status lisensi ke server online saat di layar terkunci.
     */
    public function revalidate()
    {
        $result = $this->licenseService->revalidateOnline();

        if ($result['success']) {
            return redirect()->route('dashboard')
                ->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
