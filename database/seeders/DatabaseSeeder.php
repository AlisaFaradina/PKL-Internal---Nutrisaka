<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Hanya inisialisasi akun administrator dan pool token resmi.
        // Sample seeder TIDAK otomatis dipanggil agar Real Mode dimulai bersih.
        $this->call(AdminUserSeeder::class);
    }
}
