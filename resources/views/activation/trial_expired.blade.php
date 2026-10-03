<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masa Percobaan Telah Berakhir — Nutrisaka</title>
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

        .expired-container {
            width: 100%;
            max-width: 680px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 60px -15px rgba(12, 25, 44, 0.5);
            overflow: hidden;
            text-align: center;
        }

        .expired-header {
            background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%);
            padding: 36px 30px;
            color: #ffffff;
        }

        .expired-icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .expired-body {
            padding: 36px 40px;
        }

        .safe-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-act {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-act-primary {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
        }

        .btn-act-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
        }

        .btn-act-outline {
            background: #ffffff;
            color: #0f2942;
            border: 1.5px solid #cbd5e1;
        }

        .btn-act-outline:hover {
            background: #f8fafc;
        }

        .backup-link {
            margin-top: 20px;
            display: inline-block;
            color: #64748b;
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
        }

        .backup-link:hover {
            color: #0284c7;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="expired-container">
    <div class="expired-header">
        <div class="expired-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <h1 style="font-size: 22px; margin: 0 0 8px 0; font-weight: 800;">Masa Percobaan (Trial) Telah Berakhir</h1>
        <p style="margin: 0; font-size: 14px; opacity: 0.9;">Masa uji coba gratis 7 hari untuk perangkat ini telah selesai.</p>
    </div>

    <div class="expired-body">
        <div class="safe-box">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" style="flex-shrink: 0;">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            <div>
                <strong style="color: #166534; font-size: 13.5px; display: block;">Data Usaha Anda Tetap Aman</strong>
                <span style="color: #15803d; font-size: 12.5px;">Seluruh data transaksi, pelanggan SPPG, dan riwayat mutasi Anda tetap tersimpan utuh dan TIDAK DIHAPUS.</span>
            </div>
        </div>

        <p style="color: #475569; font-size: 14.5px; line-height: 1.6; margin-bottom: 28px;">
            Untuk membuka kembali akses penuh operasional tanpa batas waktu (seumur hidup), silakan lakukan aktivasi menggunakan token lisensi resmi dari Admin.
        </p>

        <div class="btn-group">
            <a href="{{ route('onboarding') }}" class="btn-act btn-act-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                AKTIVASI LISENSI RESMI
            </a>

            <a href="{{ route('onboarding') }}" class="btn-act btn-act-outline">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                MINTA LISENSI BARU KE ADMIN
            </a>
        </div>

        <a href="{{ route('settings.export-backup') }}" class="backup-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Unduh Cadangan Basis Data (Backup SQLite)
        </a>
    </div>
</div>

</body>
</html>
