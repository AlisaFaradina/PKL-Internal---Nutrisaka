<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoBackupDatabase extends Command
{
    protected $signature = 'nutrisaka:auto-backup {--keep=7 : Jumlah file backup yang disimpan}';
    protected $description = 'Buat cadangan otomatis file database SQLite (rotasi harian)';

    public function handle(): int
    {
        $dbPath = config('database.connections.sqlite.database');

        if (!$dbPath || !file_exists($dbPath)) {
            $this->error('File database SQLite tidak ditemukan: ' . ($dbPath ?: 'path tidak dikonfigurasi'));
            return self::FAILURE;
        }

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_His');
        $backupFile = $backupDir . DIRECTORY_SEPARATOR . "nutrisaka_backup_{$timestamp}.sqlite";

        try {
            // Salin file database
            copy($dbPath, $backupFile);

            $sizeMB = round(filesize($backupFile) / 1024 / 1024, 2);
            $this->info("✅ Backup berhasil: {$backupFile} ({$sizeMB} MB)");
            Log::info("Auto-backup database berhasil: {$backupFile} ({$sizeMB} MB)");

            // Rotasi: hapus backup lama jika melebihi batas
            $this->rotateBackups($backupDir);

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('❌ Gagal membuat backup: ' . $e->getMessage());
            Log::error('Auto-backup database gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Hapus file backup yang melebihi batas rotasi.
     */
    protected function rotateBackups(string $backupDir): void
    {
        $maxKeep = (int) $this->option('keep');

        $files = glob($backupDir . DIRECTORY_SEPARATOR . 'nutrisaka_backup_*.sqlite');
        if (!$files || count($files) <= $maxKeep) {
            return;
        }

        // Urutkan berdasarkan waktu modifikasi (terlama duluan)
        usort($files, function ($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        $toDelete = array_slice($files, 0, count($files) - $maxKeep);
        foreach ($toDelete as $file) {
            if (unlink($file)) {
                $this->line("   🗑 Backup lama dihapus: " . basename($file));
            }
        }
    }
}
