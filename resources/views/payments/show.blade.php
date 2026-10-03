@extends('layouts.app')

@section('title', 'Detail Transaksi: ' . $payment->payment_number . ' — Nutrisaka')
@section('header_title', 'Detail Transaksi & Konfirmasi Pembayaran')
@section('header_subtitle', 'Rincian transaksi dan status konfirmasi pembayaran')

@section('header_actions')
    <a href="{{ route('payments.index') }}" class="btn btn-white">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <span>Kembali</span>
    </a>
    @if($payment->sale)
        <a href="{{ route('sales.show', $payment->sale) }}" class="btn btn-navy">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <span>Lihat Faktur</span>
        </a>
    @endif
@endsection

@section('content')
    <!-- Transaction Information Card -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                Informasi Transaksi
            </div>
            <div>
                @if($payment->confirmation_status === 'pending')
                    <span class="badge badge-warning">Menunggu Konfirmasi</span>
                @elseif($payment->confirmation_status === 'confirmed')
                    <span class="badge badge-success">Dikonfirmasi</span>
                @elseif($payment->confirmation_status === 'rejected')
                    <span class="badge badge-danger">Ditolak</span>
                @endif
            </div>
        </div>
        <div style="padding: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Nomor Transaksi</div>
                    <div style="font-weight: 700; color: var(--navy-800); background: var(--bg-alt); padding: 6px 12px; border-radius: var(--radius-sm); display: inline-block;">
                        {{ $payment->payment_number }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Identitas SPPG</div>
                    <div style="font-weight: 600; color: var(--navy-900);">
                        {{ $payment->sale?->sppg?->name ?? '-' }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Tanggal Transaksi</div>
                    <div style="font-weight: 600; color: var(--navy-900);">
                        {{ $payment->payment_date->format('d/m/Y') }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Jumlah Pembayaran</div>
                    <div style="font-size: 20px; font-weight: 700; color: var(--success);">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Metode Pembayaran</div>
                    <div style="font-weight: 600; color: var(--navy-900);">
                        {{ ucfirst($payment->payment_method) }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Nomor Referensi</div>
                    <div style="font-weight: 600; color: var(--navy-900);">
                        {{ $payment->reference_number ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items Card -->
    @if($payment->sale)
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div class="card-title">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    Daftar Barang / Menu yang Dipesan
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment->sale->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product->name }}</strong>
                                    <div style="font-size: 12px; color: var(--text-sub);">{{ $item->product->sku }}</div>
                                </td>
                                <td>{{ number_format($item->quantity, 2) }} {{ $item->product->unit }}</td>
                                <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td style="font-weight: 600;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: 600;">Total Pembayaran:</td>
                            <td style="font-weight: 700; font-size: 18px; color: var(--success);">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    <!-- Payment Proof & Confirmation Card -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                Bukti Pembayaran & Konfirmasi
            </div>
        </div>
        <div style="padding: 24px;">
            @if($payment->proof_file)
                <div style="margin-bottom: 24px;">
                    <div style="font-size: 14px; font-weight: 600; margin-bottom: 12px;">Bukti Pembayaran Terunggah:</div>
                    <div style="background: var(--bg-alt); padding: 16px; border-radius: var(--radius-md); display: inline-block;">
                        <a href="{{ asset('storage/' . $payment->proof_file) }}" target="_blank" style="color: var(--navy-700); text-decoration: none; display: flex; align-items: center; gap: 8px;">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                            <span>Lihat Bukti Pembayaran</span>
                        </a>
                    </div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-top: 8px;">
                        Diunggah pada: {{ $payment->payment_uploaded_at ? $payment->payment_uploaded_at->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>
            @else
                <div style="margin-bottom: 24px;">
                    <div style="font-size: 14px; font-weight: 600; margin-bottom: 12px;">Upload Bukti Pembayaran:</div>
                    <form action="{{ route('payments.upload-proof', $payment) }}" method="POST" enctype="multipart/form-data" style="display: inline;">
                        @csrf
                        <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" required style="margin-right: 12px;">
                        <button type="submit" class="btn btn-success">Upload Bukti</button>
                    </form>
                    <div style="font-size: 12px; color: var(--text-sub); margin-top: 8px;">
                        Format yang diterima: JPG, JPEG, PNG, PDF (Maksimal 5MB)
                    </div>
                </div>
            @endif

            @if($payment->confirmation_status === 'pending' && $payment->proof_file)
                <div style="padding: 20px; background: var(--warning-bg); border: 1px solid var(--warning); border-radius: var(--radius-md); margin-bottom: 24px;">
                    <div style="font-weight: 600; color: var(--warning-dark); margin-bottom: 8px;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: text-bottom;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Menunggu Konfirmasi Admin
                    </div>
                    <div style="font-size: 14px; color: var(--text-main);">
                        Bukti pembayaran telah diunggah dan menunggu konfirmasi dari admin.
                    </div>
                </div>
            @endif

            @if($payment->confirmation_status === 'confirmed')
                <div style="padding: 20px; background: var(--success-bg); border: 1px solid var(--success); border-radius: var(--radius-md); margin-bottom: 24px;">
                    <div style="font-weight: 600; color: var(--success-dark); margin-bottom: 8px;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: text-bottom;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Pembayaran Dikonfirmasi
                    </div>
                    <div style="font-size: 14px; color: var(--text-main); margin-bottom: 8px;">
                        Pembayaran telah dikonfirmasi dan masuk ke History Pembayaran.
                    </div>
                    <div style="font-size: 12px; color: var(--text-sub);">
                        Dikonfirmasi oleh: {{ $payment->confirmedBy?->name ?? 'Admin' }} pada {{ $payment->confirmed_at?->format('d/m/Y H:i') }}
                    </div>
                </div>
            @endif

            @if($payment->confirmation_status === 'rejected')
                <div style="padding: 20px; background: var(--danger-bg); border: 1px solid var(--danger); border-radius: var(--radius-md); margin-bottom: 24px;">
                    <div style="font-weight: 600; color: var(--danger-dark); margin-bottom: 8px;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: text-bottom;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        Pembayaran Ditolak
                    </div>
                    <div style="font-size: 14px; color: var(--text-main); margin-bottom: 8px;">
                        {{ $payment->rejection_reason ?? 'Tidak ada alasan ditentukan.' }}
                    </div>
                    <div style="font-size: 12px; color: var(--text-sub);">
                        Ditolak oleh: {{ $payment->confirmedBy?->name ?? 'Admin' }} pada {{ $payment->confirmed_at?->format('d/m/Y H:i') }}
                    </div>
                </div>
            @endif

            @if($payment->confirmation_status === 'pending' && $payment->proof_file)
                <div style="border-top: 1px solid var(--border); padding-top: 24px;">
                    <div style="font-size: 14px; font-weight: 600; margin-bottom: 12px;">Konfirmasi Pembayaran:</div>
                    <form action="{{ route('payments.confirm', $payment) }}" method="POST">
                        @csrf
                        <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                            <button type="submit" name="action" value="confirm" class="btn btn-success">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                Konfirmasi
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                Tolak
                            </button>
                        </div>
                        <div id="rejection-reason-container" style="display: none;">
                            <label style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Alasan Penolakan:</label>
                            <textarea name="rejection_reason" rows="3" class="form-input" style="width: 100%; resize: vertical;" placeholder="Jelaskan mengapa pembayaran ini ditolak..."></textarea>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Additional Information -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                Informasi Tambahan
            </div>
        </div>
        <div style="padding: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Catatan</div>
                    <div style="color: var(--text-main);">{{ $payment->notes ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Waktu Pembayaran</div>
                    <div style="color: var(--text-main);">{{ $payment->payment_date->format('d/m/Y H:i') }}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Waktu Upload Bukti</div>
                    <div style="color: var(--text-main);">{{ $payment->payment_uploaded_at ? $payment->payment_uploaded_at->format('d/m/Y H:i') : '-' }}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Waktu Konfirmasi</div>
                    <div style="color: var(--text-main);">{{ $payment->confirmed_at ? $payment->confirmed_at->format('d/m/Y H:i') : '-' }}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Admin yang Konfirmasi</div>
                    <div style="color: var(--text-main);">{{ $payment->confirmedBy?->name ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rejectBtn = document.querySelector('button[name="action"][value="reject"]');
            const rejectionReasonContainer = document.getElementById('rejection-reason-container');
            const rejectionReasonInput = document.querySelector('textarea[name="rejection_reason"]');

            if (rejectBtn && rejectionReasonContainer) {
                rejectBtn.addEventListener('click', function(e) {
                    rejectionReasonContainer.style.display = 'block';
                    rejectionReasonInput.required = true;
                });

                document.querySelector('button[name="action"][value="confirm"]').addEventListener('click', function() {
                    rejectionReasonContainer.style.display = 'none';
                    rejectionReasonInput.required = false;
                });
            }
        });
    </script>
@endsection
