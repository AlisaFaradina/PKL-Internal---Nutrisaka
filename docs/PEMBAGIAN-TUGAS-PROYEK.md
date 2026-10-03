# Pembagian Tugas Proyek Nutrisaka

## Hasil Analisis Repository

Repository ini adalah satu aplikasi Laravel 12 dengan NativePHP Desktop, bukan dua repository. Panel admin penerbitan token, aktivasi, data lisensi, dan aplikasi supplier saat ini berada di proyek yang sama dan memakai database lokal. Karena itu, pembagian di bawah menyesuaikan file yang benar-benar ada; bagian "Server Lisensi" pada dokumen lama dipetakan ke panel admin dan layanan token yang saat ini tertanam di Nutrisaka.

Fitur yang sudah terlihat di repository mencakup katalog dan pengaturan, SPPG dan pesanan, penjualan/pembayaran, stok/laporan, backup, onboarding/demo/trial, aktivasi lisensi, panel admin, permohonan lisensi, dan pencatatan aktivitas. Migration aplikasi berlanjut sampai `2026_10_02_000018`; urutan/nama file migration yang sudah ada tidak boleh diubah.

### Perbedaan penting dari rancangan lama

- Tidak ada proyek atau direktori Server Lisensi terpisah, `routes/api.php`, API aktivasi, `ActivationService`, `ValidationService`, `TokenGenerator`, atau `LicenseSigner`. Penerbitan dan validasi saat ini terjadi di dalam aplikasi lokal.
- Tabel `license_tokens` menyimpan nilai token di kolom `token` dan panel admin menampilkan/mengekspornya. Ini belum memenuhi aturan rancangan lama: simpan hash dan tampilkan token asli satu kali. Perlu keputusan dan pekerjaan migrasi sebelum rilis yang mengandalkan aturan tersebut.
- Aktivasi/tanda tangan berada dalam `LicenseService`; belum ada batas server-klien atau penempatan kunci privat di server terpisah seperti yang diminta rancangan lama. Jangan menganggap protokol/API server sudah tersedia.
- Ada fitur tambahan yang tidak tercakup penuh dalam pembagian lama: trial 7 hari, onboarding mode demo/real, pemisahan data simulasi, bukti pembayaran, permohonan lisensi, backup otomatis, dan log aktivitas.
- README saat ini masih README bawaan Laravel, jadi dokumen ini menjadi rujukan pembagian kerja repository.

## Pembagian Pemilik File

Kepemilikan berarti penanggung jawab utama untuk implementasi, tes, dan review modul. Perubahan lintas modul tetap perlu koordinasi dengan pemilik file bersama. Pembagian beban berdasarkan ukuran dan risiko modul, bukan menyamakan jumlah file mentah: beberapa service/kontroler lisensi jauh lebih besar daripada satu view atau migration.

### Fara — Katalog, Pengaturan, Backup, dan Fondasi Data

- Controller: `app/Http/Controllers/CategoryController.php`, `ProductController.php`, `SettingController.php`.
- Model data master/lisensi: `app/Models/Category.php`, `Product.php`, `AppSetting.php`, `License.php`, `AdminUser.php`, `LicenseToken.php`, `LicenseRequest.php`.
- Layanan/command: `app/Services/BackupService.php`, `app/Console/Commands/AutoBackupDatabase.php`.
- Migration: `2026_09_18_000001_create_app_settings_table.php`, `2026_09_18_000003_create_categories_table.php`, `2026_09_18_000004_create_products_table.php`, `2026_09_18_000011_create_licenses_table.php`, `2026_09_23_000012_create_license_tokens_table.php`, `2026_09_23_000013_create_admin_users_table.php`, `2026_10_01_000016_add_trial_fields_to_licenses_table.php`, `2026_10_02_000017_add_is_simulation_flag_to_data_tables.php`, `2026_10_02_000018_create_license_requests_table.php`.
- View: `resources/views/categories/index.blade.php`, semua view `resources/views/products/`, dan `resources/views/settings/index.blade.php`.
- Seeder data contoh: `database/seeders/NutrisakaSampleSeeder.php`.
- Tanggung jawab: konsistensi schema/model, validasi backup-restore, pengecualian data lisensi dari backup, dan perilaku data simulasi bersama Nailu.

### Aurel — SPPG, Pesanan, NativePHP, dan Klien Lisensi

- Controller: `app/Http/Controllers/SppgController.php`, `OrderController.php`, `ActivationController.php`, `LicenseController.php`.
- Model transaksi pesanan: `app/Models/Sppg.php`, `Order.php`, `OrderItem.php`.
- Service: `app/Services/OrderService.php`, `LicenseService.php`.
- Middleware/command: `app/Http/Middleware/EnsureLicensed.php`, `app/Console/Commands/CheckLicenseCommand.php`.
- Migration: `2026_09_18_000002_create_sppgs_table.php`, `2026_09_18_000005_create_orders_table.php`, `2026_09_18_000006_create_order_items_table.php`.
- View: semua view `resources/views/sppgs/`, `resources/views/orders/`, dan `resources/views/activation/`.
- NativePHP dan bootstrap middleware lisensi: `config/nativephp.php`, `app/Providers/NativeAppServiceProvider.php`, `bootstrap/app.php`.
- Test lisensi/aktivasi: `tests/Feature/LicenseAndRegistrationTest.php`.
- Tanggung jawab: alur SPPG/pesanan, aktivasi dan verifikasi lokal, trial/grace/locked, fingerprint perangkat, serta integrasi NativePHP. Sinkronkan kontrak model/migration lisensi dengan Fara.

### Ratna — Penjualan, Pembayaran, Panel Admin, dan Token

- Controller: `app/Http/Controllers/SaleController.php`, `PaymentController.php`, `app/Http/Controllers/Admin/AdminAuthController.php`, `AdminTokenController.php`, `AdminLicenseRequestController.php`.
- Model bisnis: `app/Models/Sale.php`, `SaleItem.php`, `Payment.php`.
- Service: `app/Services/SaleService.php`, `PaymentService.php`.
- Command token: `app/Console/Commands/GenerateLicenseTokenCommand.php`.
- Migration: `2026_09_18_000007_create_sales_table.php`, `2026_09_18_000008_create_sale_items_table.php`, `2026_09_18_000009_create_payments_table.php`, `2026_10_01_000015_add_payment_proof_fields_to_payments_table.php`.
- View: semua view `resources/views/sales/`, `resources/views/payments/`, `resources/views/admin/tokens/`, `resources/views/admin/requests/`, dan `resources/views/admin/login.blade.php`.
- Auth dan seed admin: `config/auth.php`, `database/seeders/AdminUserSeeder.php`, `database/seeders/DatabaseSeeder.php`.
- Test bisnis: `tests/Feature/NutrisakaBusinessLogicTest.php`.
- Pemilik integrasi route: `routes/web.php`. Anggota lain mengirim blok route dan nama route final kepada Ratna, bukan mengedit file ini secara paralel.
- Tanggung jawab: alur penjualan/pelunasan, bukti pembayaran, login/panel admin, penerbitan/revoke/reset token, ekspor CSV, dan pemrosesan permohonan lisensi. Koordinasikan perubahan tabel/model token dengan Fara dan layanan aktivasi dengan Aurel.

### Nailu — Stok, Dashboard, Laporan, Layout, dan Audit

- Controller: `app/Http/Controllers/StockController.php`, `DashboardController.php`, `ReportController.php`, `AboutController.php`.
- Model/audit: `app/Models/StockMovement.php`, `ActivityLog.php`.
- Service: `app/Services/StockService.php`.
- Middleware/trait: `app/Http/Middleware/EnsureAdminAuth.php`, `app/Traits/HasSimulationMode.php`, `LogsActivity.php`.
- Migration: `2026_09_18_000010_create_stock_movements_table.php`, `2026_09_23_000014_create_activity_logs_table.php`.
- View: semua view `resources/views/stock/`, `resources/views/dashboard/`, `resources/views/reports/`, `resources/views/about/`, `resources/views/layouts/`, `resources/views/admin/layout.blade.php`, dan `resources/views/welcome.blade.php`.
- Aset UI: `resources/css/`, `resources/js/`, `public/css/nutrisaka.css`, `public/js/medium-zoom.min.js`.
- Bootstrap pendukung: `bootstrap/providers.php`, `routes/console.php`, `app/Providers/AppServiceProvider.php`.
- Test stok: `tests/Feature/StockRoutingTest.php`.
- Tanggung jawab: stok dan mutasi, dashboard/laporan, layout bersama, pengalaman admin, filter data real/demo, dan audit log. Koordinasikan pemanggilan log dari modul anggota lain.

## File Bersama dan Aturan Integrasi

- `routes/web.php` dimiliki Ratna sebagai integrator. Aurel mengirim rute onboarding/aktivasi; Nailu mengirim rute tambahan bila diperlukan. Jangan melakukan merge blok rute tanpa pemeriksaan nama, middleware, dan route model binding.
- `bootstrap/app.php` dimiliki Aurel untuk registrasi `EnsureLicensed`; penambahan alias/middleware admin dari Nailu harus digabung melalui Aurel.
- `resources/views/settings/index.blade.php` dimiliki Fara. Bagian tombol/status lisensi tetap diintegrasikan Fara setelah menerima markup dan nama route final dari Aurel.
- `LicenseService.php` adalah batas bersama: Aurel pemilik file dan API service; Ratna koordinasi jika fungsi pembentukan/penandatanganan token perlu diubah. Jangan membuat service token baru tanpa keputusan bersama tentang arsitektur server.
- Migration baru memakai nomor berikutnya yang tersedia dan nama timestamp baru. Jangan mengganti atau menyisipkan ulang migration lama; semua migration baru harus aman untuk urutan `migrate:fresh`.
- `database/seeders/DatabaseSeeder.php` memanggil seeder admin. Fara memelihara data contoh; mode demo dan data real tidak boleh saling mengubah atau menghapus.
- File scaffold Laravel (`User`, migration users/cache/jobs, `Controller`, `ExampleTest`, `welcome` bila tidak diubah) bukan pekerjaan fitur utama dan tidak dihitung sebagai kuota kerja. `welcome.blade.php` tetap masuk kepemilikan Nailu jika disentuh.
- File perubahan lokal yang sudah ada di working tree dianggap pekerjaan tim yang sedang berjalan. Jangan menimpa atau menghapus perubahan anggota lain; cek `git status` dan bicarakan kepemilikan sebelum mengedit file milik orang lain.

## Urutan Kerja dan Pemeriksaan

1. Fara mengunci schema dasar/master dan aturan data demo; Nailu menetapkan layout serta kontrak kelas CSS bersama.
2. Aurel, Ratna, dan Nailu mengerjakan modul masing-masing setelah kontrak model, route, dan nama status disepakati.
3. Integrasi `routes/web.php` dilakukan Ratna; integrasi middleware/bootstrap lisensi dilakukan Aurel. Pemilik file menjalankan review sebelum merge.
4. Sebelum merge/demo, jalankan `php artisan migrate:fresh --seed`, `php artisan test`, `php artisan route:list`, `npm run build`, dan uji halaman modul di aplikasi NativePHP.
5. Tes yang ada saat analisis: `LicenseAndRegistrationTest`, `NutrisakaBusinessLogicTest`, dan `StockRoutingTest`, ditambah tes scaffold. Pembagian lama meminta unit test service khusus; tes tersebut belum tampak di `tests/Unit` dan perlu ditambahkan oleh pemilik modul bila skenario belum tercakup.
6. Sebelum mengklaim sistem lisensi siap produksi, sepakati pemisahan server atau bentuk arsitektur final, penyimpanan hash token/tampilan sekali, dan lokasi kunci privat. Jangan commit private key atau rahasia `.env`.