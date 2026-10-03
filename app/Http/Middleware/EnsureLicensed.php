<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLicensed
{
    public function __construct(
        protected LicenseService $licenseService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Dikecualikan untuk semua route admin dan API admin
        if ($request->is('admin*') || $request->is('api/admin*')) {
            return $next($request);
        }

        $routeName = $request->route() ? $request->route()->getName() : null;

        // Daftar nama route yang selalu dikecualikan dari pemeriksaan lisensi
        $exemptRoutes = [
            'onboarding',
            'onboarding.demo',
            'onboarding.real',
            'login',
            'login.store',
            'activation.register',
            'activation.register.store',
            'activation.register.demo',
            'activation.register.trial',
            'activation.register-request',
            'activation.locked',
            'activation.revalidate',
            'activation.request-token',
            'activation.copy-draft',
            'api.device-id',
            'trial.status',
            'trial.expired',
            'about.index',
            'about.switch-mode',
            'settings.export-backup', // Supplier selalu berhak mengunduh backup datanya sendiri
        ];

        // 2. Mode Demo: Bebaskan seluruh fitur aplikasi dengan data simulasi!
        if (AppSetting::isDemoMode()) {
            view()->share('currentLicense', null);
            view()->share('licenseStatus', 'demo');
            view()->share('trialStatus', [
                'is_trial' => false,
                'trial_active' => false,
                'trial_expired' => false,
                'days_remaining' => null,
                'time_remaining' => ['days' => 0, 'hours' => 0, 'formatted' => 'Mode Demo'],
                'trial_started_at' => null,
                'trial_ends_at' => null,
                'message' => 'Mode Demo Aktif',
            ]);
            view()->share('applicationMode', 'demo');

            return $next($request);
        }

        // Mode Asli (Real Mode)
        $verification = $this->licenseService->verifyWithTrial();

        // Bagikan data lisensi, trial, dan mode aplikasi ke seluruh view
        view()->share('currentLicense', $verification['license'] ?? null);
        view()->share('licenseStatus', $verification['status']);
        view()->share('trialStatus', $this->licenseService->getTrialStatus());
        view()->share('applicationMode', 'real');

        // Jika rute saat ini termasuk pengecualian umum, izinkan lewat
        if ($routeName && in_array($routeName, $exemptRoutes, true)) {
            return $next($request);
        }

        // 3. Belum terdaftar / first run -> arahkan ke halaman Onboarding / Pilih Mode
        if ($verification['status'] === 'unlicensed') {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Aplikasi belum terdaftar atau berlisensi.',
                    'redirect' => route('onboarding'),
                ], 403);
            }

            return redirect()->route('onboarding')
                ->with('info', 'Selamat datang di Nutrisaka! Silakan pilih mode penggunaan atau daftarkan perangkat Anda.');
        }

        // 4. Trial expired (Masa Percobaan Telah Habis)
        // Data pengguna TIDAK dihapus! Akses dibatasi ke Dashboard saja.
        if ($verification['status'] === 'trial_expired') {
            // Whitelist routes yang boleh diakses saat trial expired
            $trialExpiredAllowed = [
                'dashboard',
                'activation.request-token',
                'activation.copy-draft',
                'about.index',
                'settings.export-backup',
            ];

            $isAllowed = false;
            if ($routeName && in_array($routeName, $trialExpiredAllowed, true)) {
                $isAllowed = true;
            }

            if (!$isAllowed) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Masa uji coba (Trial 7 Hari) telah berakhir. Silakan lakukan aktivasi lisensi resmi.',
                    'redirect' => route('dashboard'),
                    'trial_expired' => true,
                    'dashboard_allowed' => true,
                ], 403);
                }

                return redirect()->route('dashboard')
                    ->with('error', 'Masa percobaan telah berakhir. Silakan lakukan aktivasi menggunakan token aktivasi untuk melanjutkan penggunaan seluruh fitur Nutrisaka.');
            }
        }

        // 5. Terkunci (Device Mismatch / License Revoked)
        if ($verification['status'] === 'locked') {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Aplikasi dalam mode terkunci. Transaksi atau perubahan data tidak diizinkan.',
                    'redirect' => route('activation.locked'),
                ], 403);
            }

            return redirect()->route('activation.locked')
                ->with('error', 'Aplikasi dalam mode terkunci. Lisensi tidak valid untuk perangkat ini atau telah dicabut.');
        }

        // 6. Masa Percobaan Aktif (Trial Active)
        // TRIAL ACCESS MATRIX:
        // Diizinkan: Dashboard, Pesanan (orders.*), Penjualan (sales.*), About, Backup export, Activation/Trial
        // Terkunci/Ditolak: Produk (products.*), Stok (stock.*), SPPG (sppgs.*), Kategori (categories.*), Laporan (reports.*), Settings, Payments
        if ($verification['status'] === 'trial') {
            $trialAllowedPrefixes = [
                'dashboard',
                'orders.',
                'sales.',
                'about.',
                'trial.',
                'activation.',
                'onboarding',
            ];

            $isAllowed = false;
            if ($routeName === 'dashboard') {
                $isAllowed = true;
            } elseif ($routeName) {
                foreach ($trialAllowedPrefixes as $prefix) {
                    if (str_starts_with($routeName, $prefix)) {
                        $isAllowed = true;
                        break;
                    }
                }
            }

            if (!$isAllowed) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Fitur ini terkunci selama Masa Percobaan (Trial). Anda memiliki akses ke Dashboard, Pesanan, dan Penjualan. Silakan aktivasi lisensi resmi untuk membuka seluruh fitur.',
                        'redirect' => route('trial.status'),
                    ], 403);
                }

                return redirect()->route('trial.status')
                    ->with('warning', 'Fitur tersebut terkunci selama Masa Percobaan (Trial 7 Hari). Anda dapat menggunakan fitur Dashboard, Pesanan, dan Penjualan secara leluasa. Untuk mengelola Produk, Stok, SPPG, dan Laporan, silakan aktivasi lisensi resmi.');
            }
        }

        return $next($request);
    }
}
