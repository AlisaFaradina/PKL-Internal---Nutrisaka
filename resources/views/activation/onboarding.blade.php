<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Mode Aplikasi — Nutrisaka Supplier SPPG</title>
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

        .onboard-container {
            width: 100%;
            max-width: 1060px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 60px -15px rgba(12, 25, 44, 0.5);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .onboard-header {
            background: linear-gradient(135deg, #0f2942 0%, #1e3a8a 65%, #0284c7 100%);
            padding: 28px 40px;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid var(--sky-400);
        }

        .onboard-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .onboard-brand-icon {
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .onboard-brand-title h1 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin: 0;
            color: #ffffff;
        }

        .onboard-brand-title p {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: var(--sky-200);
            font-weight: 500;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-btn {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }

        .onboard-content {
            padding: 36px 40px;
        }

        .hero-banner {
            text-align: center;
            margin-bottom: 32px;
        }

        .hero-banner h2 {
            font-size: 26px;
            font-weight: 800;
            color: #0f2942;
            margin: 0 0 8px 0;
        }

        .hero-banner p {
            font-size: 15px;
            color: #64748b;
            margin: 0;
            max-width: 640px;
            margin-left: auto;
            margin-right: auto;
        }

        .mode-grid {
            display: grid;
            grid-template-columns: 1fr 1.35fr;
            gap: 28px;
            align-items: stretch;
        }

        @media (max-width: 860px) {
            .mode-grid {
                grid-template-columns: 1fr;
            }
            .onboard-content {
                padding: 24px 20px;
            }
            .onboard-header {
                padding: 20px;
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
        }

        .mode-card {
            border-radius: 18px;
            border: 2px solid #e2e8f0;
            background: #ffffff;
            padding: 28px;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: all 0.25s ease;
        }

        .mode-card:hover {
            box-shadow: 0 12px 30px -8px rgba(0, 0, 0, 0.08);
        }

        .mode-card.demo {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border-color: #cbd5e1;
        }

        .mode-card.real {
            border-color: var(--sky-400);
            box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.12);
        }

        .badge-mode {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 12px;
            width: fit-content;
        }

        .badge-demo {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-real {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .card-icon.demo-icon {
            background: #e0f2fe;
            color: #0284c7;
        }

        .card-icon.real-icon {
            background: #e0e7ff;
            color: #4338ca;
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 8px 0;
        }

        .card-desc {
            font-size: 14px;
            color: #475569;
            line-height: 1.55;
            margin-bottom: 20px;
        }

        .feature-bullets {
            list-style: none;
            padding: 0;
            margin: 0 0 24px 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .feature-bullets li {
            font-size: 13px;
            color: #334155;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .feature-bullets li svg {
            flex-shrink: 0;
            margin-top: 2px;
            color: #0284c7;
        }

        .tabs-header {
            display: flex;
            gap: 8px;
            background: #f1f5f9;
            padding: 6px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .tab-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            background: transparent;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .tab-btn.active {
            background: #ffffff;
            color: #0f2942;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .device-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 14px;
        }

        .device-box label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }

        .device-flex {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .device-input {
            flex: 1;
            font-family: monospace;
            font-size: 12px;
            font-weight: 700;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            border-radius: 6px;
            color: #0f2942;
        }

        .btn-check-device {
            background: #e2e8f0;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-check-device:hover {
            background: #cbd5e1;
            color: #0f172a;
        }

        .form-group-compact {
            margin-bottom: 12px;
        }

        .form-group-compact label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 4px;
        }

        .form-input-compact {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            font-family: inherit;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        .form-input-compact:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .btn-action-primary {
            width: 100%;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border: none;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-action-primary:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
        }

        .btn-action-success {
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border: none;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-action-success:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }

        .btn-action-demo {
            width: 100%;
            background: #ffffff;
            color: #0284c7;
            border: 2px solid #0284c7;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: auto;
        }

        .btn-action-demo:hover {
            background: #0284c7;
            color: #ffffff;
        }

        .security-badge {
            margin-top: 14px;
            font-size: 11px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }
    </style>
</head>
<body>

<div class="onboard-container">
    <!-- Header -->
    <header class="onboard-header">
        <div class="onboard-brand">
            <div class="onboard-brand-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <div class="onboard-brand-title">
                <h1>NUTRISAKA</h1>
                <p>Sistem Manajemen Pasokan Bahan Pangan SPPG • Offline-First</p>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.login') }}" class="admin-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Portal Admin
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="onboard-content">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 14px; display: flex; align-items: flex-start; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink: 0; margin-top: 1px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning" style="margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: 14px; display: flex; align-items: flex-start; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink: 0; margin-top: 1px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <div>{{ session('warning') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info" style="margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; font-size: 14px; display: flex; align-items: flex-start; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink: 0; margin-top: 1px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <div>{{ session('info') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 14px; display: flex; align-items: flex-start; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink: 0; margin-top: 1px;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 14px;">
                <strong style="display: block; margin-bottom: 6px;">Terdapat kesalahan pada isian Anda:</strong>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Banner Titik Masuk -->
        <div class="hero-banner">
            <h2>Selamat Datang di Nutrisaka</h2>
            <p>Pilih mode penggunaan yang Anda butuhkan. Anda dapat mencoba dengan data contoh simulasi atau langsung mengelola data usaha operasional nyata.</p>
        </div>

        <!-- Dua Mode Grid -->
        <div class="mode-grid">

            <!-- KARTU 1: MODE DEMO -->
            <div class="mode-card demo">
                <span class="badge-mode badge-demo">Simulasi / Uji Coba</span>
                <div class="card-icon demo-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                </div>
                <h3 class="card-title">MODE DEMO</h3>
                <p class="card-desc">
                    Coba Nutrisaka dengan data contoh/simulasi lengkap. Pelajari antarmuka, pembuatan pesanan SPPG, pencetakan faktur, dan mutasi stok tanpa memengaruhi data asli.
                </p>

                <ul class="feature-bullets">
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Langsung terbuka tanpa perlu registrasi atau trial</span>
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Data contoh siap pakai (Katalog beras, telur, ayam, dll)</span>
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Data simulasi terisolasi dan tidak mencemari Mode Asli</span>
                    </li>
                </ul>

                <form action="{{ route('onboarding.demo') }}" method="POST" style="margin-top: auto;">
                    @csrf
                    <button type="submit" class="btn-action-demo">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                        MULAI MODE DEMO
                    </button>
                </form>
            </div>

            <!-- KARTU 2: MODE ASLI -->
            <div class="mode-card real">
                <span class="badge-mode badge-real">Data Usaha Riil</span>
                <div class="card-icon real-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h3 class="card-title">MODE ASLI</h3>
                <p class="card-desc">
                    Gunakan Nutrisaka dengan data operasional usaha Anda. Database bersih dan siap pakai. Trial gratis 7 hari tersedia untuk perangkat yang belum memiliki lisensi.
                </p>

                <!-- Tabs: Trial vs Daftar & Request vs Aktivasi -->
                <div class="tabs-header">
                    <button type="button" class="tab-btn active" onclick="switchTab('tab-trial')">1. Trial 7 Hari</button>
                    <button type="button" class="tab-btn" onclick="switchTab('tab-register')">2. Minta Lisensi</button>
                    <button type="button" class="tab-btn" onclick="switchTab('tab-activate')">3. Aktivasi Token</button>
                </div>

                <!-- Tab 1: Free Trial 7 Hari -->
                <div id="tab-trial" class="tab-pane active">
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 14px 16px; margin-bottom: 16px;">
                        <h4 style="margin: 0 0 6px 0; color: #166534; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Masa Uji Coba Gratis 7×24 Jam
                        </h4>
                        <p style="margin: 0; color: #15803d; font-size: 12.5px; line-height: 1.5;">
                            Perangkat Anda belum memiliki lisensi? Coba Nutrisaka sekarang juga tanpa biaya selama 7 hari penuh. Akses operasional Dashboard, Pesanan, dan Penjualan siap digunakan.
                        </p>
                    </div>

                    <form action="{{ route('activation.register.trial') }}" method="POST">
                        @csrf
                        <div class="form-group-compact">
                            <label>Nama Usaha / Supplier (Opsional):</label>
                            <input type="text" name="supplier_name" class="form-input-compact" placeholder="Contoh: CV Pangan Berkah Mandiri" value="{{ old('supplier_name', $settings['supplier_name'] ?? '') }}">
                        </div>
                        <div class="form-group-compact">
                            <label>Nomor Telepon / WhatsApp:</label>
                            <input type="text" name="supplier_phone" class="form-input-compact" placeholder="08xxxxxxxxxx" value="{{ old('supplier_phone', $settings['supplier_phone'] ?? '') }}">
                        </div>

                        <button type="submit" class="btn-action-success" style="margin-top: 14px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            MULAI TRIAL 7 HARI
                        </button>
                    </form>
                </div>

                <!-- Tab 2: Registrasi & Permohonan Lisensi Resmi -->
                <div id="tab-register" class="tab-pane">
                    <form action="{{ route('activation.register-request') }}" method="POST">
                        @csrf
                        
                        <!-- Box Device ID Aktual -->
                        <div class="device-box">
                            <label>Device ID Terdeteksi (Perangkat Fisik):</label>
                            <div class="device-flex">
                                <input type="text" id="device_id_input" name="device_id" value="{{ $deviceId }}" class="device-input" readonly required>
                                <button type="button" id="btn_check_device" class="btn-check-device" onclick="checkDeviceId()">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: -1px; margin-right: 3px;"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                                    CHECK MY DEVICE ID
                                </button>
                            </div>
                            <small id="device_status_note" style="display: block; margin-top: 4px; font-size: 11px; color: #64748b;">
                                ID terikat secara kriptografis ke mesin ini (APP ID: {{ $appId }}).
                            </small>
                        </div>

                        <div class="form-group-compact">
                            <label>Nama Usaha / Perusahaan: <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="business_name" class="form-input-compact" placeholder="Contoh: PT Nutrisi Sejahtera SPPG" value="{{ old('business_name', $settings['supplier_name'] ?? '') }}" required>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div class="form-group-compact">
                                <label>Password Akun: <span style="color:#ef4444;">*</span></label>
                                <input type="password" name="password" class="form-input-compact" placeholder="Minimal 4 karakter" required>
                            </div>
                            <div class="form-group-compact">
                                <label>Nomor WhatsApp: <span style="color:#ef4444;">*</span></label>
                                <input type="text" name="phone" class="form-input-compact" placeholder="08xxxxxxxxxx" value="{{ old('phone', $settings['supplier_phone'] ?? '') }}" required>
                            </div>
                        </div>

                        <div class="form-group-compact">
                            <label>Email Penerima Lisensi: <span style="color:#ef4444;">*</span></label>
                            <input type="email" name="email" class="form-input-compact" placeholder="emailanda@gmail.com" value="{{ old('email', $settings['supplier_email'] ?? '') }}" required>
                            <small style="color: #64748b; font-size: 11px;">Token lisensi resmi akan dikirimkan ke alamat email ini.</small>
                        </div>

                        <div class="form-group-compact">
                            <label>Alamat Usaha / Gudang (Opsional):</label>
                            <input type="text" name="address" class="form-input-compact" placeholder="Kota/Kabupaten, Provinsi" value="{{ old('address', $settings['supplier_address'] ?? '') }}">
                        </div>

                        <button type="submit" class="btn-action-primary" style="margin-top: 14px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            REGISTER & REQUEST LICENSE
                        </button>

                        <div class="security-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            <span>Permohonan otomatis diteruskan ke Admin (sakanutri@gmail.com). Password di-hash dan tidak pernah dikirim via email.</span>
                        </div>
                    </form>
                </div>

                <!-- Tab 3: Aktivasi Lisensi (Sudah Punya Token) -->
                <div id="tab-activate" class="tab-pane">
                    <form action="{{ route('login.store') }}" method="POST">
                        @csrf
                        <div class="device-box">
                            <label>Device ID Perangkat Ini:</label>
                            <input type="text" value="{{ $deviceId }}" class="device-input" style="width: 100%; box-sizing: border-box;" readonly>
                        </div>

                        <div class="form-group-compact">
                            <label>Kode Lisensi / Token Resmi dari Admin: <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="token" class="form-input-compact" style="font-family: monospace; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;" placeholder="XXXXX-XXXXX-XXXXX-XXXXX-XXXXX" required>
                            <small style="color: #64748b; font-size: 11px;">Periksa email penerimaan lisensi Anda atau hubungi admin sakanutri@gmail.com.</small>
                        </div>

                        <button type="submit" class="btn-action-primary" style="margin-top: 14px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                            AKTIVASI LISENSI
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));

        event.currentTarget.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }

    function checkDeviceId() {
        const btn = document.getElementById('btn_check_device');
        const input = document.getElementById('device_id_input');
        const note = document.getElementById('device_status_note');

        btn.innerText = 'MEMERIKSA...';
        btn.disabled = true;

        fetch('{{ route("api.device-id") }}')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.device_id) {
                    input.value = data.device_id;
                    note.innerHTML = '<span style="color: #059669; font-weight: 600;">✓ Device ID aktual terverifikasi: ' + data.device_id + '</span>';
                }
            })
            .catch(err => {
                note.innerHTML = '<span style="color: #b91c1c;">Gagal memuat Device ID secara dinamis, menggunakan nilai default perangkat.</span>';
            })
            .finally(() => {
                btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: -1px; margin-right: 3px;"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg> CHECK MY DEVICE ID';
                btn.disabled = false;
            });
    }

    // Auto-switch to tab if session just requested
    @if(session('just_requested') || session('error'))
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.tab-btn');
            if (tabs.length >= 2) {
                tabs[1].click();
            }
        });
    @endif
</script>

</body>
</html>
