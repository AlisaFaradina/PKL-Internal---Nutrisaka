<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Aktifkan PRAGMA optimisasi SQLite dan health-check pada boot.
     */
    public function boot(): void
    {
        // Hanya berlaku untuk koneksi SQLite
        if (config('database.default') !== 'sqlite') {
            return;
        }

        try {
            // 1. WAL Mode — memungkinkan read/write paralel, mencegah lock pada concurrent access
            DB::statement('PRAGMA journal_mode=WAL');

            // 2. Synchronous NORMAL — keseimbangan antara performa dan safety
            DB::statement('PRAGMA synchronous=NORMAL');

            // 3. Foreign Keys — aktifkan constraint foreign key (SQLite default: OFF)
            DB::statement('PRAGMA foreign_keys=ON');

            // 4. Busy Timeout — tunggu 5 detik jika database sedang di-lock oleh proses lain
            DB::statement('PRAGMA busy_timeout=5000');

            // 5. Cache Size — 20MB cache di memori untuk read-performance
            DB::statement('PRAGMA cache_size=-20000');

        } catch (\Throwable $e) {
            Log::warning('Gagal mengatur PRAGMA SQLite: ' . $e->getMessage());
        }

        // 6. Database Health Check (ringan — hanya pada production)
        $this->performHealthCheck();
    }

    /**
     * Jalankan pemeriksaan integritas database secara ringan.
     */
    protected function performHealthCheck(): void
    {
        try {
            // Quick integrity check (hanya cek halaman pertama)
            $result = DB::select('PRAGMA integrity_check(1)');

            if (!empty($result) && isset($result[0]->integrity_check) && $result[0]->integrity_check !== 'ok') {
                Log::error('⚠️ PERINGATAN DATABASE: Integritas database SQLite gagal! Segera buat cadangan (backup) dan periksa file database.');
            }

            // Cek ukuran file database (warning jika > 500MB)
            $dbPath = config('database.connections.sqlite.database');
            if ($dbPath && file_exists($dbPath)) {
                $sizeBytes = filesize($dbPath);
                $sizeMB = round($sizeBytes / 1024 / 1024, 1);

                if ($sizeMB > 500) {
                    Log::warning("⚠️ DATABASE BESAR: File database SQLite berukuran {$sizeMB} MB. Pertimbangkan untuk melakukan vacuum atau migrasi ke database yang lebih kuat.");
                }
            }

        } catch (\Throwable $e) {
            Log::warning('Gagal melakukan health-check database: ' . $e->getMessage());
        }
    }
}
