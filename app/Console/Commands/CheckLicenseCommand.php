<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

class CheckLicenseCommand extends Command
{
    protected $signature = 'license:check {--online : Jalankan validasi online ke server}';

    protected $description = 'Memeriksa status lisensi lokal perangkat dan masa tenggang offline';

    public function handle(LicenseService $licenseService): int
    {
        $this->info("Memeriksa status lisensi perangkat...");

        if ($this->option('online')) {
            $this->comment("Menghubungi server lisensi untuk revalidasi online...");
            $result = $licenseService->revalidateOnline();
            if ($result['success']) {
                $this->info("✓ " . $result['message']);
            } else {
                $this->error("✗ " . $result['message']);
            }
        }

        $verification = $licenseService->verifyLocal();
        $details = $licenseService->getDeviceDetails();

        $this->table(['Parameter', 'Nilai'], [
            ['ID Aplikasi', $details['app_id']],
            ['Versi Aplikasi', $details['app_version']],
            ['ID Perangkat (Hardware)', $details['device_id']],
            ['Nama Perangkat', $details['device_name']],
            ['Sistem Operasi', $details['os_name'] . ' (' . $details['os_architecture'] . ')'],
            ['Status Valid', $verification['valid'] ? 'YA (Aktif Selamanya)' : 'TIDAK (Terkunci/Belum Terdaftar)'],
            ['Status Lisensi', strtoupper($verification['status'])],
            ['Pesan', $verification['message']],
            ['Kunci Produk / Token', $verification['license'] ? $verification['license']->token_masked : 'Belum Terdaftar'],
        ]);

        return $verification['valid'] ? Command::SUCCESS : Command::FAILURE;
    }
}
