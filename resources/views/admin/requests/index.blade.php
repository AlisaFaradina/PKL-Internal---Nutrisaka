@extends('admin.layout')

@section('title', 'Permohonan Lisensi Perangkat')

@section('content')
<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 24px; font-weight: 800; color: var(--admin-navy-dark); margin: 0;">Permohonan Lisensi Perangkat</h2>
        <p style="font-size: 13.5px; color: #64748b; margin: 4px 0 0 0;">Daftar pengajuan lisensi baru dari pengguna/supplier berdasarkan Device ID fisik perangkat.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.tokens.index') }}" class="btn-admin-nav" style="background: white; color: var(--admin-navy); border-color: #cbd5e1;">
            Kelola Pool Token &rarr;
        </a>
    </div>
</div>

<!-- Stats Row -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 20px; border-left: 4px solid var(--admin-sky);">
        <div style="font-size: 12.5px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Permohonan</div>
        <div style="font-size: 28px; font-weight: 800; color: var(--admin-navy-dark); margin-top: 6px;">{{ $stats['total'] }}</div>
    </div>
    <div class="card" style="padding: 20px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 12.5px; font-weight: 700; color: #b45309; text-transform: uppercase;">Menunggu Persetujuan</div>
        <div style="font-size: 28px; font-weight: 800; color: #b45309; margin-top: 6px;">{{ $stats['pending'] }}</div>
    </div>
    <div class="card" style="padding: 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 12.5px; font-weight: 700; color: #047857; text-transform: uppercase;">Disetujui (Aktif)</div>
        <div style="font-size: 28px; font-weight: 800; color: #047857; margin-top: 6px;">{{ $stats['approved'] }}</div>
    </div>
    <div class="card" style="padding: 20px; border-left: 4px solid #ef4444;">
        <div style="font-size: 12.5px; font-weight: 700; color: #b91c1c; text-transform: uppercase;">Ditolak</div>
        <div style="font-size: 28px; font-weight: 800; color: #b91c1c; margin-top: 6px;">{{ $stats['rejected'] }}</div>
    </div>
</div>

<!-- Filter & Search Bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 24px; background: white; border-radius: 12px; border: 1px solid #e2e8f0;">
    <form method="GET" action="{{ route('admin.requests.index') }}" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 280px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama usaha, email, telepon, atau Device ID..." class="form-input" style="flex: 1; padding: 9px 14px; font-size: 13.5px;">
            <select name="status" class="form-select" style="width: auto; padding: 9px 14px; font-size: 13.5px;">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary" style="padding: 9px 18px; font-size: 13.5px;">Filter Data</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.requests.index') }}" class="btn btn-outline" style="padding: 9px 14px; font-size: 13.5px;">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Requests Table -->
<div class="card" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table class="table" style="margin: 0; width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-size: 12.5px; text-transform: uppercase;">
                    <th style="padding: 14px 18px;">ID / Tanggal</th>
                    <th style="padding: 14px 18px;">Nama Usaha & Pemohon</th>
                    <th style="padding: 14px 18px;">Kontak (Email / WA)</th>
                    <th style="padding: 14px 18px;">Device ID (Hardware)</th>
                    <th style="padding: 14px 18px;">Status</th>
                    <th style="padding: 14px 18px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr style="border-bottom: 1px solid #f1f5f9; font-size: 13.5px;">
                        <td style="padding: 14px 18px;">
                            <span style="font-weight: 700; color: var(--admin-navy);">#{{ $req->id }}</span>
                            <div style="font-size: 12px; color: #94a3b8; margin-top: 3px;">
                                {{ $req->requested_at ? $req->requested_at->format('d/m/Y H:i') : '-' }}
                            </div>
                        </td>
                        <td style="padding: 14px 18px;">
                            <strong style="color: var(--admin-navy-dark); font-size: 14px; display: block;">{{ $req->business_name }}</strong>
                            <div style="font-size: 12.5px; color: #64748b; margin-top: 2px;">{{ $req->address ?: 'Alamat belum diisi' }}</div>
                        </td>
                        <td style="padding: 14px 18px;">
                            <div style="font-weight: 600; color: #0284c7;">{{ $req->email }}</div>
                            <div style="font-size: 12.5px; color: #475569; margin-top: 2px;">{{ $req->phone }}</div>
                        </td>
                        <td style="padding: 14px 18px;">
                            <code style="font-family: monospace; font-size: 12.5px; background: #f1f5f9; padding: 4px 8px; border-radius: 4px; color: var(--admin-navy); font-weight: 600;">
                                {{ $req->device_id }}
                            </code>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 3px;">APP: {{ $req->app_id }}</div>
                        </td>
                        <td style="padding: 14px 18px;">
                            @if($req->status === 'pending')
                                <span class="badge" style="background: #fef3c7; color: #92400e; font-weight: 700; padding: 4px 10px; border-radius: 12px; font-size: 12px;">
                                    ⏳ Menunggu
                                </span>
                            @elseif($req->status === 'approved')
                                <span class="badge" style="background: #dcfce7; color: #166534; font-weight: 700; padding: 4px 10px; border-radius: 12px; font-size: 12px;">
                                    ✓ Disetujui
                                </span>
                            @else
                                <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 700; padding: 4px 10px; border-radius: 12px; font-size: 12px;">
                                    ✕ Ditolak
                                </span>
                            @endif
                        </td>
                        <td style="padding: 14px 18px; text-align: right;">
                            <a href="{{ route('admin.requests.show', $req) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 12.5px; font-weight: 600;">
                                Detail / Proses &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #94a3b8;">
                            Belum ada permohonan lisensi yang masuk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0;">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
