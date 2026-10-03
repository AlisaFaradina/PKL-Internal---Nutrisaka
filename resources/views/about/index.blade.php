@extends('layouts.app')

@section('title', 'Tentang Nutrisaka')
@section('header_title', 'Tentang Nutrisaka')
@section('header_subtitle', 'Informasi identitas aplikasi, status lisensi, dan mode sistem')

@section('content')
    <div style="max-width: 900px; margin: 0 auto;">

        <!-- Status Mode Aplikasi & Pengalihan Mode -->
        <div class="card" style="margin-bottom: 24px; border-left: 5px solid {{ $currentMode === 'demo' ? '#f59e0b' : '#0284c7' }};">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
                    Mode Penggunaan Aplikasi Saat Ini
                </div>
                <div>
                    @if($currentMode === 'demo')
                        <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 700; padding: 6px 12px; font-size: 13px;">
                            MODE DEMO (DATA SIMULASI)
                        </span>
                    @else
                        <span class="badge" style="background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; font-weight: 700; padding: 6px 12px; font-size: 13px;">
                            MODE ASLI (DATA OPERASIONAL)
                        </span>
                    @endif
                </div>
            </div>
            <div style="padding: 24px;">
                <p style="font-size: 14px; color: var(--text-main); margin-bottom: 18px; line-height: 1.6;">
                    @if($currentMode === 'demo')
                        Aplikasi saat ini berjalan dalam <strong>Mode Demo</strong>. Data produk, pesanan, dan transaksi yang tampil adalah <strong>Data Simulasi</strong>. Data operasional nyata Anda tersimpan aman dan terisolasi.
                    @else
                        Aplikasi saat ini berjalan dalam <strong>Mode Asli</strong>. Seluruh data transaksi, pelanggan SPPG, dan stok adalah <strong>Data Operasional Nyata</strong>.
                    @endif
                </p>

                <!-- Tombol Ganti Mode dengan Konfirmasi Aman -->
                <form action="{{ route('about.switch-mode') }}" method="POST" id="modeSwitchForm" onsubmit="return confirmSwitchMode(event)">
                    @csrf
                    @if($currentMode === 'demo')
                        <input type="hidden" name="mode" value="real">
                        <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            Beralih ke Mode Asli (Data Operasional)
                        </button>
                    @else
                        <input type="hidden" name="mode" value="demo">
                        <button type="submit" class="btn btn-secondary" style="font-weight: 700;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            Beralih ke Mode Demo (Data Simulasi)
                        </button>
                    @endif
                </form>
            </div>
        </div>

        <!-- Application Info Card -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div class="card-title">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    Identitas Resmi Aplikasi
                </div>
            </div>
            <div style="padding: 24px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                    <div>
                        <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Nama Aplikasi</div>
                        <div style="font-weight: 800; font-size: 17px; color: var(--navy-900);">
                            {{ $deviceDetails['app_name'] }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Versi Aplikasi</div>
                        <div style="font-weight: 600; color: var(--navy-900);">
                            v{{ $deviceDetails['app_version'] }} (Build 2026.10)
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">APP ID (Standar Lisensi)</div>
                        <div style="font-weight: 700; font-family: monospace; color: var(--navy-800); background: var(--bg-alt); padding: 6px 10px; border-radius: var(--radius-sm); display: inline-block;">
                            {{ $deviceDetails['app_id'] }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Status Tipe Data</div>
                        <div style="font-weight: 700; color: {{ $currentMode === 'demo' ? '#b45309' : '#047857' }};">
                            {{ $currentMode === 'demo' ? 'Data Simulasi / Demo' : 'Data Operasional Asli' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- License & Trial Status Card -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div class="card-title">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                    Status Lisensi & Masa Uji Coba (Trial)
                </div>
            </div>
            <div style="padding: 24px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                    <div>
                        <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Status Lisensi</div>
                        <div>
                            @if($license && !$license->is_trial && $license->isActive())
                                <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 700; padding: 4px 10px;">
                                    Active (Lifetime)
                                </span>
                            @elseif($license && $license->is_trial)
                                <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 700; padding: 4px 10px;">
                                    Trial Active
                                </span>
                            @else
                                <span class="badge" style="background: #fee2e2; color: #b91c1c; font-weight: 700; padding: 4px 10px;">
                                    Inactive / Unlicensed
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Status Trial 7 Hari</div>
                        <div style="font-weight: 700;">
                            @if($trialStatus['trial_active'])
                                <span style="color: #15803d;">Aktif (Sisa: {{ $trialStatus['time_remaining']['formatted'] }})</span>
                            @elseif($trialStatus['trial_expired'])
                                <span style="color: #b91c1c;">Telah Kedaluwarsa (Expired)</span>
                            @else
                                <span style="color: #64748b;">Belum Digunakan</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Waktu Trial Dimulai</div>
                        <div style="font-weight: 600; color: var(--navy-900);">
                            @if(!empty($trialStatus['trial_started_at']))
                                {{ \Carbon\Carbon::parse($trialStatus['trial_started_at'])->translatedFormat('d F Y • H:i') }} WIB
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Waktu Trial Berakhir</div>
                        <div style="font-weight: 600; color: var(--navy-900);">
                            @if(!empty($trialStatus['trial_ends_at']))
                                {{ \Carbon\Carbon::parse($trialStatus['trial_ends_at'])->translatedFormat('d F Y • H:i') }} WIB
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>

                @if(!$license || ($license->is_trial && $trialStatus['trial_expired']))
                    <div style="margin-top: 20px; padding: 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
                        <div>
                            <div style="font-weight: 700; color: #1e40af; font-size: 14px;">Ingin mengaktifkan lisensi resmi permanen?</div>
                            <div style="font-size: 13px; color: #3b82f6;">Daftarkan perangkat Anda atau masukkan kode token dari Admin.</div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('onboarding') }}" class="btn btn-primary" style="font-size: 13px; padding: 8px 14px;">Aktivasi / Minta Lisensi</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Device Information Card -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div class="card-title">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Informasi Perangkat Keras (Device Binding)
                </div>
            </div>
            <div style="padding: 24px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                    <div>
                        <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Device ID (Hardware ID)</div>
                        <div style="font-weight: 700; font-family: monospace; color: var(--navy-800); background: var(--bg-alt); padding: 6px 10px; border-radius: var(--radius-sm); word-break: break-all;">
                            {{ $deviceDetails['device_id'] }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Nama Perangkat</div>
                        <div style="font-weight: 600; color: var(--navy-900);">
                            {{ $deviceDetails['device_name'] }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Sistem Operasi</div>
                        <div style="font-weight: 600; color: var(--navy-900);">
                            {{ $deviceDetails['os_name'] }} ({{ $deviceDetails['os_architecture'] }})
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Database Engine</div>
                        <div style="font-weight: 600; color: var(--navy-900);">
                            {{ $deviceDetails['database_engine'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function confirmSwitchMode(e) {
            const current = '{{ $currentMode }}';
            let message = '';
            if (current === 'demo') {
                message = "Anda akan beralih ke MODE ASLI (Data Operasional Nyata). Data operasional usaha Anda akan ditampilkan. Lanjutkan?";
            } else {
                message = "Data contoh akan digunakan untuk simulasi/demo. Data ini bukan data operasional nyata. Lanjutkan beralih ke Mode Demo?";
            }

            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
            return true;
        }
    </script>
@endsection
