<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\License;
use App\Models\LicenseToken;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    /**
     * Identitas Aplikasi Resmi Nutrisaka
     */
    public const APPLICATION_ID = 'APP-NUTRISAKA-SPPG-2026';
    public const APPLICATION_NAME = 'Nutrisaka - Sistem Manajemen Supplier SPPG';
    public const APPLICATION_VERSION = '1.0.4';

    /**
     * Kunci rahasia internal / public key fallback untuk verifikasi tanda tangan token.
     */
    protected const DEFAULT_SIGN_KEY = 'NUTRISAKA-OFFLINE-LICENSE-VERIFIER-KEY-2026';

    /**
     * Mengambil Machine GUID Windows dari Registry.
     */
    public function getMachineGuid(): ?string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            try {
                $regCmd = file_exists('C:\\Windows\\System32\\reg.exe')
                    ? 'C:\\Windows\\System32\\reg.exe query "HKLM\\SOFTWARE\\Microsoft\\Cryptography" /v MachineGuid 2>NUL'
                    : 'reg query "HKLM\\SOFTWARE\\Microsoft\\Cryptography" /v MachineGuid 2>NUL';

                $regOutput = @shell_exec($regCmd);
                if ($regOutput && preg_match('/MachineGuid\s+REG_SZ\s+([a-zA-Z0-9\-]+)/i', $regOutput, $matches)) {
                    return trim($matches[1]);
                }
            } catch (\Throwable $e) {
                // Ignore fallback
            }
        }

        // Fallback Linux / Unix machine-id
        if (file_exists('/etc/machine-id')) {
            $id = trim(@file_get_contents('/etc/machine-id'));
            if (!empty($id)) {
                return $id;
            }
        }

        return null;
    }

    /**
     * Menghasilkan ID Perangkat (Device ID) standar format GUID seperti di Windows System Information.
     * Contoh: D104FAD6-4DDD-4694-875E-58E1C19BF837
     */
    public function getDeviceId(): string
    {
        $guid = $this->getMachineGuid();
        if ($guid) {
            return strtoupper(trim($guid));
        }

        $hash = strtoupper(hash('sha256', php_uname('n') . '|' . php_uname('s') . '|' . php_uname('m') . '|' . gethostname()));
        return substr($hash, 0, 8) . '-' . substr($hash, 8, 4) . '-' . substr($hash, 12, 4) . '-' . substr($hash, 16, 4) . '-' . substr($hash, 20, 12);
    }

    /**
     * Menghasilkan Device Fingerprint unik dan stabil untuk kriptografi lisensi.
     */
    public function getDeviceFingerprint(): string
    {
        $components = [];

        $guid = $this->getMachineGuid();
        if ($guid) {
            $components[] = 'GUID:' . $guid;
        }

        $components[] = 'OS:' . php_uname('s');
        $components[] = 'HOST:' . php_uname('n');
        $components[] = 'ARCH:' . php_uname('m');
        $components[] = 'NAME:' . (gethostname() ?: 'NUTRI-HOST');
        $components[] = 'COMP:' . (getenv('COMPUTERNAME') ?: getenv('HOSTNAME') ?: 'NUTRI-PC');

        return hash('sha256', implode('|#|', $components));
    }

    /**
     * Menghasilkan representasi ringkas sidik perangkat (misal: NDEV-D104-FAD6-875E).
     */
    public function getShortFingerprint(): string
    {
        $guid = $this->getDeviceId();
        $clean = str_replace('-', '', $guid);
        return 'NDEV-' . substr($clean, 0, 4) . '-' . substr($clean, 4, 4) . '-' . substr($clean, 8, 4);
    }

    /**
     * Mendapatkan informasi spesifikasi lengkap perangkat fisik yang sedang aktif.
     */
    public function getDeviceDetails(): array
    {
        $guid = $this->getMachineGuid();
        $deviceId = $this->getDeviceId();
        $shortId = $this->getShortFingerprint();
        $computerName = getenv('COMPUTERNAME') ?: gethostname() ?: php_uname('n');

        return [
            'app_id'            => self::APPLICATION_ID,
            'app_name'          => self::APPLICATION_NAME,
            'app_version'       => self::APPLICATION_VERSION,
            'device_id'         => $deviceId,
            'short_device_id'   => $shortId,
            'device_name'       => $computerName,
            'machine_guid'      => $guid ?: 'Tersedia melalui fallback hardware',
            'os_name'           => $this->detectOperatingSystem(),
            'os_architecture'   => php_uname('m') ?: '64-bit (x64)',
            'processor'         => getenv('PROCESSOR_IDENTIFIER') ?: (php_uname('m') . ' Multi-Core Processor'),
            'php_version'       => PHP_VERSION,
            'database_engine'   => 'SQLite 3 (Mode WAL Aktif & Sinkronisasi Normal)',
            'installation_path' => base_path(),
        ];
    }

    /**
     * Mendeteksi nama ramah sistem operasi yang sedang berjalan.
     */
    public function detectOperatingSystem(): string
    {
        $os = PHP_OS;
        if (strtoupper(substr($os, 0, 3)) === 'WIN') {
            $release = php_uname('r');
            $version = php_uname('v');
            if (version_compare($release, '10.0', '>=')) {
                if (preg_match('/build\s*(\d+)/i', $version, $m) && (int) $m[1] >= 22000) {
                    return "Windows 11 (Build {$m[1]})";
                }
                return "Windows 10 / 11 (Kernel {$release})";
            }
            return "Windows ({$release})";
        }

        return php_uname('s') . ' ' . php_uname('r');
    }

    /**
     * Menyamarkan token / product key untuk keamanan tampilan (misal: NTRS1-•••••-•••••-•••••-9MNOP).
     */
    public function maskToken(string $token): string
    {
        $clean = strtoupper(trim($token));
        $parts = explode('-', $clean);

        if (count($parts) === 5) {
            return $parts[0] . '-•••••-•••••-•••••-' . $parts[4];
        }

        if (count($parts) >= 4) {
            return $parts[0] . '-••••-••••-' . end($parts);
        }

        if (strlen($clean) >= 8) {
            return substr($clean, 0, 4) . '••••' . substr($clean, -4);
        }

        return '••••-••••-VALID';
    }

    /**
     * Memeriksa apakah perangkat ini sudah terdaftar lisensinya.
     */
    public function isRegistered(): bool
    {
        $license = License::first();
        return $license !== null && $license->status !== 'locked';
    }

    /**
     * Memeriksa apakah ini adalah first run aplikasi.
     */
    public function isFirstRun(): bool
    {
        $license = License::first();
        if (!$license) {
            return true;
        }
        return $license->first_run_at === null;
    }

    /**
     * Memulai trial 7 hari untuk aplikasi dengan proteksi anti-curang.
     */
    public function startTrial(): void
    {
        $license = License::first();
        $fingerprint = $this->getDeviceFingerprint();
        $deviceId = $this->getDeviceId();

        // Cek backup file untuk mencegah reset trial dengan menghapus database
        $backup = $this->readTrialBackup();
        if ($backup) {
            // Restore dari backup jika database dihapus
            $trialStartedAt = Carbon::parse($backup['trial_started_at']);
            $trialEndsAt = Carbon::parse($backup['trial_ends_at']);
            $trialSignature = $backup['signature'];
        } else {
            $trialStartedAt = now();
            $trialEndsAt = now()->addDays(7);
            $trialSignature = $this->generateTrialSignature($deviceId, $trialStartedAt, $trialEndsAt);
            $this->saveTrialBackup($deviceId, $trialStartedAt, $trialEndsAt, $trialSignature);
        }

        if ($license) {
            // Jika lisensi resmi aktif sudah ada, jangan ganti ke trial
            if (!$license->is_trial && $license->isActive()) {
                return;
            }

            // Jika trial sudah pernah dimulai sebelumnya, pertahankan tanggal asli dan JANGAN reset!
            if ($license->trial_started_at !== null) {
                return;
            }

            $license->update([
                'is_trial' => true,
                'trial_started_at' => $trialStartedAt,
                'trial_ends_at' => $trialEndsAt,
                'first_run_at' => $license->first_run_at ?? now(),
                'last_seen_at' => now(),
                'trial_signature' => $trialSignature,
                'device_fingerprint' => $fingerprint,
                'status' => 'active',
            ]);
        } else {
            License::create([
                'token_masked' => 'TRIAL-' . $this->getShortFingerprint(),
                'license_payload' => json_encode([
                    'app_id' => self::APPLICATION_ID,
                    'app_version' => self::APPLICATION_VERSION,
                    'device_id' => $deviceId,
                    'device_fingerprint' => $fingerprint,
                    'license_type' => 'Trial (7 Days)',
                    'trial_days' => 7,
                    'issued_at' => $trialStartedAt->toIso8601String(),
                    'expires_at' => $trialEndsAt->toIso8601String(),
                ]),
                'signature' => $this->signPayload(['trial' => true, 'fingerprint' => $fingerprint]),
                'device_fingerprint' => $fingerprint,
                'status' => 'active',
                'is_trial' => true,
                'trial_started_at' => $trialStartedAt,
                'trial_ends_at' => $trialEndsAt,
                'first_run_at' => now(),
                'last_seen_at' => now(),
                'trial_signature' => $trialSignature,
                'activated_at' => now(),
                'last_validated_at' => now(),
            ]);
        }
    }

    /**
     * Generate HMAC signature untuk trial data.
     */
    private function generateTrialSignature(string $deviceId, $trialStartedAt, $trialEndsAt): string
    {
        $data = $deviceId . '|' . $trialStartedAt->toIso8601String() . '|' . $trialEndsAt->toIso8601String();
        return hash_hmac('sha256', $data, self::DEFAULT_SIGN_KEY);
    }

    /**
     * Verifikasi trial signature.
     */
    private function verifyTrialSignature(string $deviceId, $trialStartedAt, $trialEndsAt, string $signature): bool
    {
        $expected = $this->generateTrialSignature($deviceId, $trialStartedAt, $trialEndsAt);
        return hash_equals($expected, $signature);
    }

    /**
     * Simpan salinan trial data ke file (backup copy).
     */
    private function saveTrialBackup(string $deviceId, $trialStartedAt, $trialEndsAt, string $signature): void
    {
        $backupDir = storage_path('app/trial_backup');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupFile = $backupDir . '/trial_backup.json';
        $backupData = [
            'device_id' => $deviceId,
            'trial_started_at' => $trialStartedAt->toIso8601String(),
            'trial_ends_at' => $trialEndsAt->toIso8601String(),
            'signature' => $signature,
            'saved_at' => now()->toIso8601String(),
        ];

        file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
    }

    /**
     * Baca salinan trial backup dari file.
     */
    private function readTrialBackup(): ?array
    {
        $backupFile = storage_path('app/trial_backup/trial_backup.json');
        if (!file_exists($backupFile)) {
            return null;
        }

        $content = file_get_contents($backupFile);
        $data = json_decode($content, true);

        if (!is_array($data)) {
            return null;
        }

        return $data;
    }

    /**
     * Update last_seen_at untuk deteksi clock rollback.
     */
    public function updateLastSeen(): void
    {
        $license = License::first();
        if (!$license) {
            return;
        }

        $now = now();
        $lastSeen = $license->last_seen_at;

        // Deteksi clock rollback
        if ($lastSeen && $now->lt($lastSeen)) {
            // Jam dimundurkan - gunakan last_seen sebagai waktu efektif
            // Atau tandai sebagai curang
            $license->update([
                'last_seen_at' => $lastSeen,
                'status' => 'locked',
            ]);
            return;
        }

        $license->update(['last_seen_at' => $now]);
    }

    /**
     * Cek apakah perangkat ini sudah pernah melakukan trial (per-device, bukan per-account).
     */
    public function hasDeviceTrial(): bool
    {
        $backup = $this->readTrialBackup();
        if ($backup) {
            return true;
        }

        $license = License::first();
        if ($license && $license->is_trial) {
            return true;
        }

        return false;
    }

    /**
     * Memeriksa status trial lengkap dengan verifikasi anti-curang.
     */
    public function getTrialStatus(): array
    {
        $license = License::first();

        if (!$license) {
            return [
                'is_trial' => false,
                'trial_active' => false,
                'trial_expired' => false,
                'days_remaining' => null,
                'time_remaining' => ['days' => 0, 'hours' => 0, 'formatted' => '-'],
                'trial_started_at' => null,
                'trial_ends_at' => null,
                'message' => 'No license found',
            ];
        }

        // Update last_seen untuk deteksi clock rollback
        $this->updateLastSeen();

        if (!$license->is_trial) {
            return [
                'is_trial' => false,
                'trial_active' => false,
                'trial_expired' => false,
                'days_remaining' => null,
                'time_remaining' => ['days' => 0, 'hours' => 0, 'formatted' => 'Lisensi Penuh (Lifetime)'],
                'trial_started_at' => $license->trial_started_at,
                'trial_ends_at' => $license->trial_ends_at,
                'message' => 'Licensed (not trial)',
            ];
        }

        // Verifikasi signature trial data
        if ($license->trial_signature) {
            $deviceId = $this->getDeviceId();
            $signatureValid = $this->verifyTrialSignature(
                $deviceId,
                $license->trial_started_at,
                $license->trial_ends_at,
                $license->trial_signature
            );

            if (!$signatureValid) {
                // Data trial dimodifikasi manual - tandai sebagai expired
                return [
                    'is_trial' => true,
                    'trial_active' => false,
                    'trial_expired' => true,
                    'days_remaining' => 0,
                    'time_remaining' => ['days' => 0, 'hours' => 0, 'formatted' => 'Telah Berakhir'],
                    'trial_started_at' => $license->trial_started_at,
                    'trial_ends_at' => $license->trial_ends_at,
                    'message' => 'Trial data integrity check failed',
                ];
            }
        }

        $isActive = $license->isTrialActive();
        $isExpired = $license->isTrialExpired();
        $daysRemaining = $license->trialDaysRemaining();
        $timeRemaining = $license->trialTimeRemaining();

        return [
            'is_trial' => true,
            'trial_active' => $isActive,
            'trial_expired' => $isExpired,
            'days_remaining' => $daysRemaining,
            'time_remaining' => $timeRemaining,
            'trial_started_at' => $license->trial_started_at,
            'trial_ends_at' => $license->trial_ends_at,
            'message' => $isActive ? "Trial active - {$timeRemaining['formatted']} remaining" : 'Trial expired',
        ];
    }

    /**
     * Verifikasi lisensi dengan pengecekan trial.
     */
    public function verifyWithTrial(): array
    {
        $license = License::first();

        // Belum ada lisensi / first run
        if (!$license) {
            return [
                'valid' => false,
                'status' => 'unlicensed',
                'message' => 'Aplikasi belum terdaftar. Silakan aktivasi token lisensi atau mulai masa percobaan gratis 7 hari.',
                'license' => null,
            ];
        }

        // Check if trial is active
        if ($license->is_trial && $license->isTrialActive()) {
            return [
                'valid' => true,
                'status' => 'trial',
                'message' => 'Trial aktif. Sisa waktu: ' . $license->trialDaysRemaining() . ' hari.',
                'license' => $license,
            ];
        }

        // Check if trial is expired
        if ($license->is_trial && $license->isTrialExpired()) {
            return [
                'valid' => false,
                'status' => 'trial_expired',
                'message' => 'Masa percobaan telah berakhir. Silakan lakukan aktivasi menggunakan token aktivasi untuk melanjutkan penggunaan seluruh fitur Nutrisaka.',
                'license' => $license,
            ];
        }

        // Regular license verification
        return $this->verifyLocal();
    }

    /**
     * Verifikasi lisensi secara lokal (offline-first & lifetime).
     * Lisensi berlaku selamanya untuk perangkat ini kecuali:
     * 1. Belum terdaftar (unlicensed)
     * 2. Perangkat fisik berbeda (device mismatch)
     * 3. Dicabut oleh Admin (revoked)
     * 4. Integritas tanda tangan rusak
     */
    public function verifyLocal(): array
    {
        $license = License::first();

        if (!$license) {
            return [
                'valid' => false,
                'status' => 'unlicensed',
                'message' => 'Aplikasi belum terdaftar. Silakan masukkan token lisensi resmi Anda.',
                'license' => null,
            ];
        }

        $currentFingerprint = $this->getDeviceFingerprint();

        // 1. Cek kecocokan sidik perangkat (mencegah database disalin ke perangkat lain)
        if ($license->device_fingerprint !== $currentFingerprint) {
            return [
                'valid' => false,
                'status' => 'locked',
                'message' => 'Perangkat Berbeda Terdeteksi! Lisensi ini terikat pada perangkat fisik lain. Setiap perangkat (komputer desktop, android, dll) membutuhkan token lisensi tersendiri. Silakan hubungi sakanutri@gmail.com.',
                'license' => $license,
            ];
        }

        // 2. Verifikasi tanda tangan digital payload
        if (!$this->verifySignature($license->license_payload, $license->signature)) {
            return [
                'valid' => false,
                'status' => 'locked',
                'message' => 'Integritas data lisensi tidak valid atau telah dimodifikasi.',
                'license' => $license,
            ];
        }

        // 3. Cek apakah token telah dicabut oleh Administrator di tabel license_tokens
        $tokenRecord = LicenseToken::where('device_fingerprint', $currentFingerprint)->first();
        if ($tokenRecord && $tokenRecord->isRevoked()) {
            if ($license->status !== 'locked') {
                $license->update(['status' => 'locked']);
            }
            return [
                'valid' => false,
                'status' => 'locked',
                'message' => 'Lisensi ini telah dicabut atau dinonaktifkan oleh Administrator Nutrisaka. Hubungi sakanutri@gmail.com.',
                'license' => $license,
            ];
        }

        // 4. Cek masa berlaku lisensi jika ada expires_at di dalam payload
        $payload = json_decode($license->license_payload, true);
        if (is_array($payload) && !empty($payload['expires_at'])) {
            $expiresAt = Carbon::parse($payload['expires_at']);
            if ($expiresAt->isPast()) {
                if ($license->status !== 'locked') {
                    $license->update(['status' => 'locked']);
                }
                return [
                    'valid' => false,
                    'status' => 'locked',
                    'message' => 'Masa berlaku lisensi ini telah berakhir pada ' . $expiresAt->format('d/m/Y') . '. Silakan perbarui lisensi Anda.',
                    'license' => $license,
                ];
            }
        }

        // Lisensi aktif selamanya (permanen)
        if ($license->status !== 'active') {
            $license->update(['status' => 'active']);
        }

        return [
            'valid' => true,
            'status' => 'active',
            'message' => 'Lisensi aktif selamanya untuk perangkat ini.',
            'license' => $license,
        ];
    }

    /**
     * Mendaftarkan dan mengaktivasi token lisensi di perangkat ini.
     */
    public function activate(string $token, array $supplierData): array
    {
        $token = strtoupper(trim($token));

        // Validasi format token / Product Key (Windows 25-char key, standard NTRS token, atau demo)
        $isWindowsKey = (bool) preg_match('/^[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}$/', $token);
        $isLegacyToken = (bool) preg_match('/^NTRS-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $token);
        $isDemo = (bool) preg_match('/^NTRS-DEMO-[A-Z0-9\-]+$/', $token);

        if (!$isWindowsKey && !$isLegacyToken && !$isDemo) {
            throw new Exception('Format Kunci Produk tidak valid. Masukkan 25-karakter Product Key (contoh: NTRS1-7K2MQ-9XA4P-LD8RT-2W9BC) atau Token Lisensi (contoh: NTRS-7K2M-Q9XA-4PLD).');
        }

        $fingerprint = $this->getDeviceFingerprint();
        $serverUrl = config('services.license_server.url') ?: env('LICENSE_SERVER_URL');

        $isDemo = (app()->environment('local', 'testing') && str_starts_with($token, 'NTRS-DEMO'));

        // 1. Validasi ke tabel license_tokens lokal
        $tokenRecord = LicenseToken::where('token', $token)->first();

        if ($tokenRecord) {
            if ($tokenRecord->isRevoked()) {
                throw new Exception('Token lisensi ini telah dicabut oleh Administrator Nutrisaka. Hubungi sakanutri@gmail.com untuk meminta token baru.');
            }

            if (!empty($tokenRecord->expires_at) && Carbon::parse($tokenRecord->expires_at)->isPast()) {
                throw new Exception('Token lisensi ini telah kedaluwarsa pada ' . Carbon::parse($tokenRecord->expires_at)->format('d/m/Y') . '. Silakan gunakan token yang masih berlaku.');
            }

            if ($tokenRecord->isActive()) {
                // Jika sudah aktif pada perangkat fisik yang berbeda
                if ($tokenRecord->device_fingerprint && $tokenRecord->device_fingerprint !== $fingerprint) {
                    $devName = $tokenRecord->device_name ?: 'Perangkat Lain';
                    throw new Exception("Token ini sudah terikat dan digunakan pada {$devName}. Satu token hanya berlaku untuk satu perangkat fisik. Jika ingin menggunakan pada perangkat ini, silakan ajukan token baru ke sakanutri@gmail.com.");
                }
            }

            // Kunci token ke sidik perangkat ini
            $tokenRecord->update([
                'status' => 'active',
                'device_fingerprint' => $fingerprint,
                'device_name' => php_uname('s') . ' - ' . (gethostname() ?: 'Perangkat') . ' (' . (php_uname('m') ?: 'x64') . ')',
                'supplier_name' => $supplierData['supplier_name'] ?? $tokenRecord->supplier_name,
                'pic_name' => $supplierData['pic_name'] ?? $tokenRecord->pic_name,
                'contact' => $supplierData['supplier_phone'] ?? $tokenRecord->contact,
                'activated_at' => Carbon::now(),
            ]);
        } else {
            // Jika token belum ada di tabel lokal (misal token resmi baru yang di-generate offline oleh admin),
            // daftarkan token ini ke tabel license_tokens
            LicenseToken::create([
                'token' => $token,
                'status' => 'active',
                'device_fingerprint' => $fingerprint,
                'device_name' => php_uname('s') . ' - ' . (gethostname() ?: 'Perangkat') . ' (' . (php_uname('m') ?: 'x64') . ')',
                'supplier_name' => $supplierData['supplier_name'] ?? 'Supplier SPPG',
                'pic_name' => $supplierData['pic_name'] ?? '',
                'contact' => $supplierData['supplier_phone'] ?? '',
                'notes' => 'Aktivasi langsung di perangkat',
                'activated_at' => Carbon::now(),
            ]);
        }

        // 2. Jika URL License Server dikonfigurasi, hubungi server
        if ($serverUrl && !$isDemo) {
            try {
                $response = Http::timeout(10)->post(rtrim($serverUrl, '/') . '/api/activate', [
                    'token' => $token,
                    'device_fingerprint' => $fingerprint,
                    'device_name' => gethostname(),
                    'business_name' => $supplierData['supplier_name'] ?? '',
                    'contact' => $supplierData['supplier_phone'] ?? '',
                    'app_version' => '1.0.1',
                ]);

                if ($response->failed()) {
                    $errorMsg = $response->json('message') ?? 'Aktivasi ditolak oleh Server Lisensi. Pastikan token belum digunakan di perangkat lain.';
                    throw new Exception($errorMsg);
                }

                $responseData = $response->json();
                $payload = $responseData['payload'] ?? [];
                $signature = $responseData['signature'] ?? '';
            } catch (Exception $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw new Exception('Gagal menghubungi Server Lisensi: ' . $e->getMessage());
            }
        } else {
            // Mode Offline / Lokal: HMAC Verification
            $payload = [
                'app_id' => self::APPLICATION_ID,
                'app_version' => self::APPLICATION_VERSION,
                'token' => $token,
                'device_id' => $this->getDeviceId(),
                'device_fingerprint' => $fingerprint,
                'device_name' => getenv('COMPUTERNAME') ?: gethostname() ?: 'NUTRI-PC',
                'business_name' => $supplierData['supplier_name'] ?? 'Supplier SPPG',
                'pic_name' => $supplierData['pic_name'] ?? '',
                'license_type' => 'Digital License (Lifetime Hardware Bound)',
                'issued_at' => Carbon::now()->toIso8601String(),
                'expires_at' => null, // Lifetime selamanya
                'plan' => 'standard_single_device',
                'max_devices' => 1,
            ];
            $signature = $this->signPayload($payload);
        }

        $payloadJson = json_encode($payload);

        // 3. Simpan lisensi lokal di database — PERMANEN (grace_until = null)
        $license = License::first();
        if ($license) {
            $license->update([
                'token_masked' => $this->maskToken($token),
                'license_payload' => $payloadJson,
                'signature' => $signature,
                'device_fingerprint' => $fingerprint,
                'status' => 'active',
                'is_trial' => false,
                'trial_started_at' => null,
                'trial_ends_at' => null,
                'activated_at' => Carbon::now(),
                'last_validated_at' => Carbon::now(),
                'grace_until' => null, // Selamanya
            ]);
        } else {
            $license = License::create([
                'token_masked' => $this->maskToken($token),
                'license_payload' => $payloadJson,
                'signature' => $signature,
                'device_fingerprint' => $fingerprint,
                'status' => 'active',
                'is_trial' => false,
                'trial_started_at' => null,
                'trial_ends_at' => null,
                'activated_at' => Carbon::now(),
                'last_validated_at' => Carbon::now(),
                'grace_until' => null, // Selamanya
            ]);
        }

        // Update profil usaha supplier di app_settings
        if (!empty($supplierData['supplier_name'])) {
            AppSetting::set('supplier_name', $supplierData['supplier_name']);
        }
        if (!empty($supplierData['pic_name'])) {
            AppSetting::set('supplier_pic', $supplierData['pic_name']);
        }
        if (!empty($supplierData['supplier_phone'])) {
            AppSetting::set('supplier_phone', $supplierData['supplier_phone']);
        }
        if (!empty($supplierData['supplier_address'])) {
            AppSetting::set('supplier_address', $supplierData['supplier_address']);
        }
        if (!empty($supplierData['pin'])) {
            AppSetting::setPin($supplierData['pin']);
        }

        return [
            'success' => true,
            'message' => 'Registrasi token lisensi berhasil! Aplikasi aktif selamanya pada perangkat ini.',
            'license' => $license,
        ];
    }

    /**
     * Memvalidasi ulang lisensi (sinkronisasi status).
     */
    public function revalidateOnline(): array
    {
        $license = License::first();
        if (!$license) {
            return ['success' => false, 'message' => 'Aplikasi belum memiliki lisensi.'];
        }

        $fingerprint = $this->getDeviceFingerprint();

        // Cek apakah token dicabut oleh admin di database lokal
        $tokenRecord = LicenseToken::where('device_fingerprint', $fingerprint)->first();
        if ($tokenRecord && $tokenRecord->isRevoked()) {
            $license->update(['status' => 'locked']);
            return ['success' => false, 'message' => 'Lisensi ini telah dicabut oleh Administrator Nutrisaka.'];
        }

        $serverUrl = config('services.license_server.url') ?: env('LICENSE_SERVER_URL');

        if ($serverUrl) {
            try {
                $response = Http::timeout(8)->post(rtrim($serverUrl, '/') . '/api/validate', [
                    'device_fingerprint' => $fingerprint,
                    'token_masked' => $license->token_masked,
                ]);

                if ($response->successful()) {
                    $license->update([
                        'status' => 'active',
                        'last_validated_at' => Carbon::now(),
                        'grace_until' => null,
                    ]);

                    return ['success' => true, 'message' => 'Status lisensi berhasil divalidasi.'];
                }

                if ($response->status() === 403 || $response->json('status') === 'revoked') {
                    $license->update(['status' => 'locked']);
                    return ['success' => false, 'message' => 'Lisensi telah dicabut atau dinonaktifkan oleh Administrator.'];
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal revalidasi online lisensi: ' . $e->getMessage());
            }
        }

        // Pengecekan integritas lokal
        $verification = $this->verifyLocal();
        if ($verification['valid']) {
            $license->update([
                'status' => 'active',
                'last_validated_at' => Carbon::now(),
                'grace_until' => null,
            ]);
            return ['success' => true, 'message' => 'Status lisensi sah dan aktif selamanya untuk perangkat ini.'];
        }

        return ['success' => false, 'message' => $verification['message']];
    }

    /**
     * Menonaktifkan perangkat ini (logout / lepas lisensi).
     * Token akan dilepas dari perangkat ini sehingga bisa digunakan kembali.
     */
    public function deactivate(): bool
    {
        $license = License::first();
        if (!$license) {
            return true;
        }

        $fingerprint = $this->getDeviceFingerprint();

        // Lepas binding perangkat pada tabel license_tokens
        $tokenRecord = LicenseToken::where('device_fingerprint', $fingerprint)->first();
        if ($tokenRecord) {
            $tokenRecord->update([
                'status' => 'unused',
                'device_fingerprint' => null,
                'device_name' => null,
                'notes' => ($tokenRecord->notes ? $tokenRecord->notes . ' | ' : '') . 'Perangkat dilepas/logout pada ' . Carbon::now()->format('d/m/Y H:i'),
            ]);
        }

        $serverUrl = config('services.license_server.url') ?: env('LICENSE_SERVER_URL');
        if ($serverUrl) {
            try {
                Http::timeout(6)->post(rtrim($serverUrl, '/') . '/api/deactivate', [
                    'device_fingerprint' => $fingerprint,
                    'token_masked' => $license->token_masked,
                ]);
            } catch (\Throwable $e) {
                // Ignore error
            }
        }

        $license->delete();
        return true;
    }

    /**
     * Menandatangani payload secara kriptografis (HMAC-SHA256).
     */
    public function signPayload(array $payload, ?string $key = null): string
    {
        $key = $key ?: (env('LICENSE_SIGNING_KEY') ?: self::DEFAULT_SIGN_KEY);
        ksort($payload);
        $dataToSign = json_encode($payload, JSON_UNESCAPED_SLASHES);
        return hash_hmac('sha256', $dataToSign, $key);
    }

    /**
     * Memverifikasi keabsahan tanda tangan kriptografis.
     */
    public function verifySignature(string $payloadJson, string $signature): bool
    {
        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            return false;
        }

        $expected = $this->signPayload($payload);
        return hash_equals($expected, $signature);
    }

    /**
     * Helper untuk membuat Windows-style Product Key 25-karakter (misal: NTRS1-7K2MQ-9XA4P-LD8RT-2W9BC).
     */
    public static function generateWindowsProductKey(): string
    {
        $charset = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $blocks = ['NTRS1'];

        for ($b = 1; $b < 5; $b++) {
            $block = '';
            for ($i = 0; $i < 5; $i++) {
                $block .= $charset[random_int(0, strlen($charset) - 1)];
            }
            $blocks[] = $block;
        }

        return implode('-', $blocks);
    }

    /**
     * Helper untuk membuat token acak resmi yang mudah dibaca (misal: NTRS-7K2M-Q9XA-4PLD).
     */
    public static function generateTokenString(): string
    {
        // Karakter tanpa huruf ambigu (tanpa 0, O, 1, I)
        $charset = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $blocks = [];

        for ($b = 0; $b < 3; $b++) {
            $block = '';
            for ($i = 0; $i < 4; $i++) {
                $block .= $charset[random_int(0, strlen($charset) - 1)];
            }
            $blocks[] = $block;
        }

        return 'NTRS-' . implode('-', $blocks);
    }
}
