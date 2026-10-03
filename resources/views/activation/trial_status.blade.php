<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Masa Uji Coba (Trial 7 Hari) — Nutrisaka</title>
    <link rel="stylesheet" href="{{ asset('css/nutrisaka.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(145deg, #0c192c 0%, #132743 45%, #0369a1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
        }

        .status-container {
            width: 100%;
            max-width: 820px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 60px -15px rgba(12, 25, 44, 0.5);
            overflow: hidden;
        }

        .status-header {
            background: linear-gradient(135deg, #0f2942 0%, #1e3a8a 65%, #0284c7 100%);
            padding: 28px 36px;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid var(--sky-400);
        }

        .status-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .status-brand-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .status-body {
            padding: 36px 40px;
        }

        .countdown-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #86efac;
            border-radius: 18px;
            padding: 24px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .countdown-title {
            font-size: 13px;
            font-weight: 700;
            color: #166534;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }

        .countdown-value {
            font-size: 28px;
            font-weight: 800;
            color: #14532d;
        }

        .timeline-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 28px;
        }

        .timeline-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
        }

        .timeline-box label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            display: block;
            margin-bottom: 4px;
        }

        .timeline-box .value {
            font-size: 14px;
            font-weight: 700;
            color: #0f2942;
        }

        .matrix-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f2942;
            margin: 0 0 14px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .matrix-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 32px;
        }

        .matrix-col {
            background: #ffffff;
            border-radius: 14px;
            padding: 18px;
            border: 1px solid #e2e8f0;
        }

        .matrix-col.allowed {
            border-left: 4px solid #10b981;
        }

        .matrix-col.locked {
            border-left: 4px solid #94a3b8;
            background: #fafaf9;
        }

        .matrix-header {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .matrix-header.allowed-header {
            color: #065f46;
        }

        .matrix-header.locked-header {
            color: #475569;
        }

        .matrix-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 13px;
        }

        .matrix-list li {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-flex {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-flex .btn-primary {
            flex: 1.2;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            padding: 13px 20px;
            border-radius: 10px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-flex .btn-outline {
            flex: 1;
            background: #ffffff;
            color: #0f2942;
            border: 1.5px solid #cbd5e1;
            padding: 13px 20px;
            border-radius: 10px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="status-container">
    <div class="status-header">
        <div class="status-brand">
            <div class="status-brand-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div>
                <h1 style="font-size: 20px; margin: 0; font-weight: 800;">STATUS TRIAL NUTRISAKA</h1>
                <p style="margin: 2px 0 0 0; font-size: 12px; color: var(--sky-200);">Masa Percobaan Resmi 7×24 Jam</p>
            </div>
        </div>
        <div>
            <span style="background: rgba(16, 185, 129, 0.2); border: 1px solid #34d399; color: #ffffff; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;">
                TRIAL AKTIF
            </span>
        </div>
    </div>

    <div class="status-body">
        @if(session('warning'))
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 12px 16px; margin-bottom: 24px; color: #92400e; font-size: 13px;">
                {{ session('warning') }}
            </div>
        @endif

        <!-- Sisa Waktu Countdown -->
        <div class="countdown-card">
            <div>
                <div class="countdown-title">Sisa Waktu Masa Percobaan</div>
                <div class="countdown-value">{{ $timeRemaining['formatted'] ?? '7 hari' }}</div>
                <div style="font-size: 12px; color: #15803d; margin-top: 4px;">
                    Perhitungan real-time dari server/database. Waktu tidak ter-reset saat aplikasi ditutup.
                </div>
            </div>
            <div style="width: 56px; height: 56px; border-radius: 50%; background: #ffffff; display: flex; align-items: center; justify-content: center; color: #16a34a; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
        </div>

        <!-- Timeline Dimulai & Berakhir -->
        <div class="timeline-grid">
            <div class="timeline-box">
                <label>Dimulai Pada</label>
                <div class="value">
                    @if(!empty($trialStatus['trial_started_at']))
                        {{ \Carbon\Carbon::parse($trialStatus['trial_started_at'])->translatedFormat('d F Y • H:i') }} WIB
                    @else
                        {{ now()->translatedFormat('d F Y • H:i') }} WIB
                    @endif
                </div>
            </div>
            <div class="timeline-box">
                <label>Berakhir Pada</label>
                <div class="value">
                    @if(!empty($trialStatus['trial_ends_at']))
                        {{ \Carbon\Carbon::parse($trialStatus['trial_ends_at'])->translatedFormat('d F Y • H:i') }} WIB
                    @else
                        {{ now()->addDays(7)->translatedFormat('d F Y • H:i') }} WIB
                    @endif
                </div>
            </div>
        </div>

        <!-- Matriks Akses Fitur -->
        <div class="matrix-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            Hak Akses Fitur Selama Masa Percobaan
        </div>

        <div class="matrix-grid">
            <div class="matrix-col allowed">
                <div class="matrix-header allowed-header">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Fitur Terbuka (Aktif)
                </div>
                <ul class="matrix-list">
                    <li><span style="color:#10b981; font-weight:bold;">✓</span> Dashboard & Statistik Ringkasan</li>
                    <li><span style="color:#10b981; font-weight:bold;">✓</span> Pesanan SPPG (Input & Kelola)</li>
                    <li><span style="color:#10b981; font-weight:bold;">✓</span> Penjualan & Faktur Pengiriman</li>
                    <li><span style="color:#10b981; font-weight:bold;">✓</span> Cetak Dokumen & Unduh Cadangan Data</li>
                    <li><span style="color:#10b981; font-weight:bold;">✓</span> Informasi Tentang Aplikasi</li>
                </ul>
            </div>

            <div class="matrix-col locked">
                <div class="matrix-header locked-header">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Fitur Dibatasi (Memerlukan Lisensi)
                </div>
                <ul class="matrix-list">
                    <li><span style="color:#94a3b8; font-weight:bold;">🔒</span> Produk & Katalog Bahan Baku</li>
                    <li><span style="color:#94a3b8; font-weight:bold;">🔒</span> Stok Masuk, Adjustment, & Riwayat Mutasi</li>
                    <li><span style="color:#94a3b8; font-weight:bold;">🔒</span> Daftar Pelanggan SPPG Lengkap</li>
                    <li><span style="color:#94a3b8; font-weight:bold;">🔒</span> Laporan Rekapitulasi Finansial Penuh</li>
                    <li><span style="color:#94a3b8; font-weight:bold;">🔒</span> Panel Pengaturan Administrator</li>
                </ul>
            </div>
        </div>

        <!-- Tombol Tindakan -->
        <div class="btn-flex">
            <a href="{{ route('dashboard') }}" class="btn-primary">
                LANJUT KE DASHBOARD
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
            <a href="{{ route('onboarding') }}" class="btn-outline">
                AKTIVASI LISENSI RESMI
            </a>
        </div>
    </div>
</div>

</body>
</html>
