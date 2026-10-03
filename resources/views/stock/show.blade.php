@extends('layouts.app')

@section('title', 'Detail Stok: ' . $product->name . ' — Nutrisaka')
@section('header_title', 'Detail Stok Gudang')
@section('header_subtitle', 'Rincian persediaan dan riwayat keluar-masuk stok')

@section('header_actions')
    <a href="{{ route('stock.index') }}" class="btn btn-white">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <span>Kembali</span>
    </a>
    <a href="{{ route('stock.create-in') }}?product_id={{ $product->id }}" class="btn btn-success">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
        <span>+ Barang Masuk</span>
    </a>
    <a href="{{ route('stock.create-adjustment') }}?product_id={{ $product->id }}" class="btn btn-warning">
        <span>Opname Stok</span>
    </a>
@endsection

@section('content')
    <!-- Product Detail Card -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                Informasi Barang
            </div>
        </div>
        <div style="padding: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">SKU</div>
                    <div style="font-weight: 700; color: var(--navy-800); background: var(--bg-alt); padding: 6px 12px; border-radius: var(--radius-sm); display: inline-block;">
                        {{ $product->sku }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Nama Barang</div>
                    <div style="font-size: 18px; font-weight: 600; color: var(--navy-900);">
                        {{ $product->name }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Kategori</div>
                    <div>
                        <span class="badge badge-secondary">{{ $product->category->name }}</span>
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Satuan</div>
                    <div style="font-weight: 600; color: var(--navy-900);">
                        {{ $product->unit }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Stok Minimum</div>
                    <div style="font-weight: 600; color: var(--navy-900);">
                        {{ $product->min_stock }} {{ $product->unit }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Harga Jual</div>
                    <div style="font-weight: 600; color: var(--navy-900);">
                        Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Harga Beli</div>
                    <div style="font-weight: 600; color: var(--navy-900);">
                        Rp {{ number_format($product->cost_price, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Status</div>
                    <div>
                        @if($product->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-danger">Non-Aktif</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($product->description)
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
                    <div style="font-size: 12px; color: var(--text-sub); margin-bottom: 4px;">Deskripsi</div>
                    <div style="color: var(--text-main);">{{ $product->description }}</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Stock Status Card -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Status Ketersediaan Stok
            </div>
        </div>
        <div style="padding: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px;">
                <div style="text-align: center; padding: 20px; background: var(--bg-alt); border-radius: var(--radius-md);">
                    <div style="font-size: 14px; color: var(--text-sub); margin-bottom: 8px;">Stok Tersedia</div>
                    <div style="font-size: 32px; font-weight: 700; color: {{ $product->current_stock <= $product->min_stock ? 'var(--danger)' : 'var(--navy-900)' }};">
                        {{ $product->current_stock }}
                    </div>
                    <div style="font-size: 14px; color: var(--text-sub);">{{ $product->unit }}</div>
                </div>
                <div style="text-align: center; padding: 20px; background: var(--bg-alt); border-radius: var(--radius-md);">
                    <div style="font-size: 14px; color: var(--text-sub); margin-bottom: 8px;">Status Ketersediaan</div>
                    <div style="font-size: 24px; font-weight: 600; color: {{ $product->stock_status === 'aman' ? 'var(--success)' : ($product->stock_status === 'menipis' ? 'var(--warning)' : 'var(--danger)') }};">
                        {{ $product->stock_status_label }}
                    </div>
                </div>
                <div style="text-align: center; padding: 20px; background: var(--bg-alt); border-radius: var(--radius-md);">
                    <div style="font-size: 14px; color: var(--text-sub); margin-bottom: 8px;">Selisih dari Minimum</div>
                    <div style="font-size: 32px; font-weight: 700; color: {{ $product->current_stock - $product->min_stock >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $product->current_stock - $product->min_stock >= 0 ? '+' : '' }}{{ number_format($product->current_stock - $product->min_stock, 2) }}
                    </div>
                    <div style="font-size: 14px; color: var(--text-sub);">{{ $product->unit }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movement History -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Riwayat Keluar-Masuk Stok
            </div>
            <a href="{{ route('stock.history') }}?product_id={{ $product->id }}" class="btn btn-sm btn-outline">
                Lihat Semua Riwayat &rarr;
            </a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Sebelum</th>
                        <th>Sesudah</th>
                        <th>Referensi</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockMovements as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($movement->type === 'in')
                                    <span class="badge badge-success">{{ $movement->type_label }}</span>
                                @elseif($movement->type === 'out')
                                    <span class="badge badge-danger">{{ $movement->type_label }}</span>
                                @else
                                    <span class="badge badge-warning">{{ $movement->type_label }}</span>
                                @endif
                            </td>
                            <td style="font-weight: 600;">
                                {{ $movement->type === 'out' ? '-' : '+' }}{{ number_format($movement->quantity, 2) }} {{ $product->unit }}
                            </td>
                            <td>{{ number_format($movement->before_stock, 2) }} {{ $product->unit }}</td>
                            <td style="font-weight: 600;">{{ number_format($movement->after_stock, 2) }} {{ $product->unit }}</td>
                            <td>
                                @if($movement->reference_type)
                                    <span style="font-size: 12px; color: var(--text-sub);">
                                        {{ ucfirst($movement->reference_type) }}
                                        @if($movement->reference_id)
                                            #{{ $movement->reference_id }}
                                        @endif
                                    </span>
                                @else
                                    <span style="color: var(--text-light);">-</span>
                                @endif
                            </td>
                            <td style="max-width: 300px;">
                                <div style="font-size: 13px; color: var(--text-main); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $movement->notes ?? '-' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-light); padding: 40px;">
                                Belum ada riwayat mutasi stok untuk barang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 16px 20px;">
            {{ $stockMovements->links() }}
        </div>
    </div>
@endsection
