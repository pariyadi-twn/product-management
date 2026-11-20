@extends('layouts.app')

@section('title', 'Detail Aktivitas')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('activity-logs.index') }}">Catatan Aktivitas</a></li>
    <li class="breadcrumb-item active" aria-current="page">Detail</li>
@endsection

@php
    $labels = [
        'sku' => 'SKU',
        'name' => 'Nama produk',
        'category_id' => 'Kategori',
        'category_name' => 'Kategori',
        'price' => 'Harga',
        'stock' => 'Stok',
        'status' => 'Status',
        'description' => 'Deskripsi',
    ];

    $old = $log->old_values ?? [];
    $new = $log->new_values ?? [];
    $isProductAction = in_array($log->action, ['create', 'update', 'delete'], true);

    $displayKeys = [];

    if ($isProductAction) {
        $displayKeys = array_unique(array_merge(array_keys($old), array_keys($new)));

        if (array_key_exists('category_name', $old) || array_key_exists('category_name', $new)) {
            $displayKeys = array_values(array_filter($displayKeys, fn ($key) => $key !== 'category_id'));
        }
    }

    $formatValue = function (string $key, mixed $value): string {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($key) {
            'price' => 'Rp '.number_format((float) $value, 0, ',', '.'),
            'stock' => number_format((int) $value, 0, ',', '.').' unit',
            'status' => ucfirst((string) $value),
            default => (string) $value,
        };
    };
@endphp

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $log->actionBadgeClass() }}">
                            {{ $log->actionLabel() }}@if($log->isFailedLogin()) (gagal)@endif
                        </span>
                        <span class="fw-semibold">{{ $log->description }}</span>
                    </div>
                    <span class="text-secondary small">{{ $log->created_at?->format('d/m/Y H:i:s') }}</span>
                </div>
                <div class="card-body">
                    @if($isProductAction)
                        @if($displayKeys === [])
                            <p class="text-secondary mb-0">Tidak ada rincian nilai yang tersimpan.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 22%">Isian</th>
                                            <th>Nilai Lama</th>
                                            <th>Nilai Baru</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($displayKeys as $key)
                                            @php
                                                $oldValue = $old[$key] ?? null;
                                                $newValue = $new[$key] ?? null;
                                            @endphp
                                            <tr>
                                                <td class="fw-semibold">{{ $labels[$key] ?? $key }}</td>
                                                <td class="{{ filled($oldValue) ? 'activity-diff-old' : '' }}">
                                                    {{ filled($oldValue) ? $formatValue($key, $oldValue) : '—' }}
                                                </td>
                                                <td class="{{ filled($newValue) ? 'activity-diff-new' : '' }}">
                                                    {{ filled($newValue) ? $formatValue($key, $newValue) : '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @else
                        @if($log->contextSummary() !== [])
                            <dl class="row mb-0">
                                @foreach($log->contextSummary() as $key => $value)
                                    <dt class="col-sm-3 text-capitalize">{{ $key }}</dt>
                                    <dd class="col-sm-9"><code class="text-break">{{ $value }}</code></dd>
                                @endforeach
                            </dl>
                        @else
                            <p class="text-secondary mb-0">Tidak ada rincian tambahan.</p>
                        @endif
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Catatan Aktivitas
                    </a>
                    @if($log->product && ! $log->product->trashed())
                        <a href="{{ route('products.show', $log->product) }}" class="btn btn-outline-primary btn-sm ms-1">
                            <i class="bi bi-box-seam me-1"></i>Lihat Produk
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><span class="fw-semibold">Informasi Aktivitas</span></div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-5">Pelaku</dt>
                        <dd class="col-7">{{ $log->user?->name ?? 'Tidak dikenal' }}</dd>

                        <dt class="col-5">Email</dt>
                        <dd class="col-7 text-break">{{ $log->user?->email ?? '—' }}</dd>

                        <dt class="col-5">Waktu</dt>
                        <dd class="col-7">{{ $log->created_at?->format('d/m/Y H:i:s') ?? '—' }}</dd>

                        <dt class="col-5">Alamat IP</dt>
                        <dd class="col-7"><code>{{ $log->ip_address ?? '—' }}</code></dd>

                        <dt class="col-5">Perangkat</dt>
                        <dd class="col-7 text-break">{{ $log->user_agent ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
