<?php

namespace App\Console\Commands;

use App\Models\LicenseToken;
use App\Services\LicenseService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateProductKeyCommand extends Command
{
    protected $signature = 'license:generate-key 
                            {--device-id= : ID Perangkat / Hardware GUID target pembeli}
                            {--customer= : Nama supplier / pelanggan}
                            {--contact= : Nomor WhatsApp / kontak supplier}
                            {--notes= : Catatan administrasi}';

    protected $description = 'Menerbitkan Kunci Produk (Product Key) 25-Karakter model Windows yang terikat pada ID Perangkat';

    public function handle(LicenseService $licenseService): int
    {
        $deviceId = $this->option('device-id') ? strtoupper(trim($this->option('device-id'))) : null;
        $customer = $this->option('customer') ?: 'Supplier SPPG Mitra';
        $contact = $this->option('contact') ?: '';
        $notes = $this->option('notes') ?: ($deviceId ? "Kunci terikat khusus untuk Device ID: {$deviceId}" : 'Kunci produk terbuka untuk 1 perangkat fisik');

        // Buat Kunci Produk format Windows: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX
        do {
            $key = LicenseService::generateWindowsProductKey();
        } while (LicenseToken::where('token', $key)->exists());

        // Simpan ke database token lokal
        LicenseToken::create([
            'token'              => $key,
            'status'             => 'unused',
            'device_fingerprint' => $deviceId ? hash('sha256', 'GUID:' . strtolower($deviceId)) : null,
            'device_name'        => $deviceId ? "Perangkat Terikat ({$deviceId})" : null,
            'supplier_name'      => $customer,
            'contact'            => $contact,
            'notes'              => $notes,
        ]);

        $this->info("=================================================================");
        $this->info("      KUNCI PRODUK (PRODUCT KEY) RESMI NUTRISAKA TERBIT          ");
        $this->info("=================================================================");
        $this->line("<fg=yellow;options=bold>ID Aplikasi      :</> " . LicenseService::APPLICATION_ID);
        $this->line("<fg=yellow;options=bold>Product Key (25c):</> <fg=green;options=bold>{$key}</>");
        $this->line("<fg=yellow>Pelanggan/Usaha  :</> {$customer}");
        $this->line("<fg=yellow>Kontak           :</> " . ($contact ?: '-'));
        $this->line("<fg=yellow>Target Device ID :</> " . ($deviceId ?: 'Belum terikat (akan terikat otomatis saat aktivasi pertama)'));
        $this->line("<fg=yellow>Masa Berlaku     :</> Permanen / Seumur Hidup (Lifetime)");
        $this->line("<fg=yellow>Tipe Lisensi     :</> Windows Hardware-Bound Digital License (1 PC)");
        $this->info("=================================================================");
        $this->comment("Salin Product Key di atas dan berikan ke Supplier untuk diinput pada Halaman Aktivasi.");

        return Command::SUCCESS;
    }
}
