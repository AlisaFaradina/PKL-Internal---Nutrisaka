@extends('admin.layout')

@section('title', 'Detail Permohonan Lisensi #' . $licenseRequest->id)

@section('content')
<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <a href="{{ route('admin.requests.index') }}" style="color: #0284c7; text-decoration: none; font-size: 13.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            &larr; Kembali ke Daftar Permohonan
        </a>
        <h2 style="font-size: 24px; font-weight: 800; color: var(--admin-navy-dark); margin: 0;">
            Permohonan Lisensi #{{ $licenseRequest->id }} — {{ $licenseRequest->business_name }}
        </h2>
    </div>
    <div>
        @if($licenseRequest->status === 'pending')
            <span class="badge" style="background: #fef3c7; color: #92400e; font-weight: 700; padding: 6px 14px; border-radius: 20px; font-size: 13px;">
                ⏳ Status: Menunggu Persetujuan
            </span>
        @elseif($licenseRequest->status === 'approved')
            <span class="badge" style="background: #dcfce7; color: #166534; font-weight: 700; padding: 6px 14px; border-radius: 20px; font-size: 13px;">
                ✓ Status: Lisensi Telah Diterbitkan
            </span>
        @else
            <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 700; padding: 6px 14px; border-radius: 20px; font-size: 13px;">
                ✕ Status: Ditolak
            </span>
        @endif
    </div>
</div>

@if(session('generated_key'))
    <div style="background: #ecfdf5; border: 2px solid #10b981; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="font-size: 14px; font-weight: 700; color: #065f46; margin-bottom: 6px;">
            🎉 KODE LISENSI RESMI BERHASIL DITERBITKAN:
        </div>
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <input type="text" id="generatedKeyField" value="{{ session('generated_key') }}" readonly style="font-family: monospace; font-size: 18px; font-weight: 800; color: #0f172a; padding: 10px 16px; border-radius: 8px; border: 1px solid #6ee7b7; background: white; width: 340px;">
            <button type="button" class="btn btn-primary" onclick="copyLicenseKey()" style="padding: 10px 18px;">
                Salin Kode
            </button>
            @if(session('wa_link'))
                <a href="{{ session('wa_link') }}" target="_blank" class="btn" style="background: #25d366; color: white; padding: 10px 18px; font-weight: 700; text-decoration: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
                    <span>Kirim via WhatsApp</span> &rarr;
                </a>
            @endif
        </div>
    </div>
@endif

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Informasi Pemohon -->
    <div class="card" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px;">
        <h3 style="font-size: 17px; font-weight: 700; color: var(--admin-navy-dark); margin: 0 0 18px 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
            Data Pemohon & Spesifikasi Perangkat
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 14px;">
            <div>
                <span style="color: #64748b; font-size: 12.5px; display: block; margin-bottom: 4px;">Nama Usaha / Perusahaan:</span>
                <strong style="color: var(--admin-navy-dark); font-size: 16px;">{{ $licenseRequest->business_name }}</strong>
            </div>
            <div>
                <span style="color: #64748b; font-size: 12.5px; display: block; margin-bottom: 4px;">Email Pemohon:</span>
                <strong style="color: #0284c7;">{{ $licenseRequest->email }}</strong>
            </div>
            <div>
                <span style="color: #64748b; font-size: 12.5px; display: block; margin-bottom: 4px;">Nomor Telepon / WhatsApp:</span>
                <strong>{{ $licenseRequest->phone }}</strong>
            </div>
            <div>
                <span style="color: #64748b; font-size: 12.5px; display: block; margin-bottom: 4px;">Waktu Pengajuan:</span>
                <span>{{ $licenseRequest->requested_at ? $licenseRequest->requested_at->translatedFormat('d F Y, H:i') : '-' }}</span>
            </div>
            <div style="grid-column: 1 / -1;">
                <span style="color: #64748b; font-size: 12.5px; display: block; margin-bottom: 4px;">Alamat Usaha:</span>
                <span>{{ $licenseRequest->address ?: 'Tidak ada alamat' }}</span>
            </div>
        </div>

        <hr style="border: none; border-top: 1px dashed #e2e8f0; margin: 20px 0;">

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
            <div style="font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 10px;">
                Identifikasi Kriptografis Hardware:
            </div>
            <div style="margin-bottom: 8px;">
                <span style="font-size: 12px; color: #64748b;">Device ID (Physical Binding):</span>
                <div style="font-family: monospace; font-size: 15px; font-weight: 700; color: var(--admin-navy-dark); margin-top: 2px;">
                    {{ $licenseRequest->device_id }}
                </div>
            </div>
            <div>
                <span style="font-size: 12px; color: #64748b;">Target Application ID:</span>
                <div style="font-family: monospace; font-size: 13px; font-weight: 600; color: #64748b;">
                    {{ $licenseRequest->app_id }}
                </div>
            </div>
        </div>

        @if($licenseRequest->license_token)
            <div style="margin-top: 20px; padding: 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
                <span style="font-size: 12.5px; color: #166534; font-weight: 700;">Kode Lisensi yang Telah Diterbitkan:</span>
                <div style="font-family: monospace; font-size: 18px; font-weight: 800; color: #15803d; margin-top: 4px;">
                    {{ $licenseRequest->license_token }}
                </div>
                <div style="font-size: 12px; color: #4ade80; margin-top: 4px;">
                    Diterbitkan pada: {{ $licenseRequest->processed_at ? $licenseRequest->processed_at->format('d/m/Y H:i') : '-' }}
                </div>
            </div>
        @endif
    </div>

    <!-- Panel Tindakan Administrator -->
    <div>
        <div class="card" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px;">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--admin-navy-dark); margin: 0 0 16px 0;">
                Tindakan Administrator
            </h3>

            @if($licenseRequest->status === 'pending')
                <form action="{{ route('admin.requests.generate', $licenseRequest) }}" method="POST" style="margin-bottom: 16px;">
                    @csrf
                    <p style="font-size: 13px; color: #64748b; margin-bottom: 14px;">
                        Setujui permohonan ini dan buat kunci lisensi resmi 25-karakter yang terikat secara permanen ke Device ID pemohon.
                    </p>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 14px; font-weight: 700;">
                        ✓ GENERATE LISENSI
                    </button>
                </form>

                <form action="{{ route('admin.requests.reject', $licenseRequest) }}" method="POST" onsubmit="return confirm('Tolak permohonan lisensi ini?');">
                    @csrf
                    <div style="margin-bottom: 12px;">
                        <input type="text" name="notes" placeholder="Alasan penolakan (opsional)" class="form-input" style="font-size: 13px; padding: 8px 12px;">
                    </div>
                    <button type="submit" class="btn btn-danger" style="width: 100%; padding: 8px; font-size: 13px;">
                        ✕ Tolak Permohonan
                    </button>
                </form>
            @elseif($licenseRequest->status === 'approved')
                <div style="color: #166534; font-size: 13.5px; line-height: 1.5;">
                    <p>Permohonan ini telah disetujui. Token lisensi telah dikirimkan ke email <strong>{{ $licenseRequest->email }}</strong>.</p>
                </div>

                @php
                    $cleanPhone = preg_replace('/[^0-9]/', '', $licenseRequest->phone);
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    }
                    $waText = urlencode("Halo {$licenseRequest->business_name}, lisensi resmi Nutrisaka untuk perangkat Device ID: {$licenseRequest->device_id} telah aktif! Kode Lisensi Anda: {$licenseRequest->license_token}. Masukkan kode ini di halaman aktivasi aplikasi Nutrisaka.");
                    $waLink = "https://wa.me/{$cleanPhone}?text={$waText}";
                @endphp

                <a href="{{ $waLink }}" target="_blank" class="btn" style="background: #25d366; color: white; width: 100%; box-sizing: border-box; text-align: center; text-decoration: none; padding: 10px; font-weight: 700; border-radius: 8px; margin-top: 10px; display: block;">
                    Kirim Ulang via WhatsApp
                </a>
            @else
                <div style="color: #991b1b; font-size: 13.5px;">
                    <p>Permohonan ini telah ditolak. Catatan: {{ $licenseRequest->notes ?: '-' }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function copyLicenseKey() {
        const field = document.getElementById('generatedKeyField');
        if (field) {
            navigator.clipboard.writeText(field.value).then(() => {
                alert('Kode Lisensi berhasil disalin: ' + field.value);
            });
        }
    }
</script>
@endsection
