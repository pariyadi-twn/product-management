@extends('layouts.app')

@section('title', 'Catatan Aktivitas')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Catatan Aktivitas</li>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-2 align-items-end">
                <div class="col-6 col-lg-2">
                    <label for="action" class="form-label small">Jenis aktivitas</label>
                    <select id="action" name="action" class="form-select" data-auto-submit>
                        <option value="">Semua jenis</option>
                        @foreach($actionLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['action'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-3">
                    <label for="product" class="form-label small">Produk (nama/SKU)</label>
                    <input
                        type="search"
                        id="product"
                        name="product"
                        value="{{ $filters['product'] }}"
                        class="form-control"
                        placeholder="Cari produk terkait…"
                        maxlength="100"
                    >
                </div>
                <div class="col-6 col-lg-2">
                    <label for="date_from" class="form-label small">Dari tanggal</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                </div>
                <div class="col-6 col-lg-2">
                    <label for="date_to" class="form-label small">Sampai tanggal</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                </div>
                <div class="col-12 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>Saring
                    </button>
                    @if($hasActiveFilters)
                        <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary" title="Hapus filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span class="fw-semibold">Riwayat Aktivitas</span>
            <span class="text-secondary small">{{ $logs->total() }} catatan</span>
        </div>
        <div class="card-body p-0">
            @if($logs->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="fs-1 text-secondary mb-2"><i class="bi bi-clock-history"></i></div>
                    <h2 class="h5">Belum ada catatan aktivitas</h2>
                    <p class="text-secondary mb-0">Aktivitas pengguna akan muncul di sini setelah ada operasi yang tercatat.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Jenis</th>
                                <th>Deskripsi</th>
                                <th>Produk</th>
                                <th>Pelaku</th>
                                <th>IP</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td class="text-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge {{ $log->actionBadgeClass() }}">
                                            {{ $log->actionLabel() }}@if($log->isFailedLogin()) (gagal)@endif
                                        </span>
                                    </td>
                                    <td>{{ $log->description }}</td>
                                    <td>
                                        @if($log->product && ! $log->product->trashed())
                                            <a href="{{ route('products.show', $log->product) }}" class="text-decoration-none">
                                                {{ $log->product->name }}
                                            </a>
                                        @elseif($log->product)
                                            {{ $log->product->name }}
                                            <span class="badge text-bg-secondary ms-1">dihapus</span>
                                        @else
                                            <span class="text-secondary">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->user?->name ?? 'Tidak dikenal' }}</td>
                                    <td><code class="small">{{ $log->ip_address ?? '—' }}</code></td>
                                    <td class="text-end">
                                        <a href="{{ route('activity-logs.show', $log) }}" class="btn btn-outline-secondary btn-sm" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($logs->hasPages())
            <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="text-secondary small">
                    Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} catatan
                </div>
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
