@extends('layouts.app')

@section('title', 'Pantau Performa')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Pantau Performa</li>
@endsection

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-label">Operasi Hari Ini</div>
                            <div class="metric-value">{{ number_format($summary['operations'], 0, ',', '.') }}</div>
                        </div>
                        <div class="fs-1 text-primary opacity-50"><i class="bi bi-arrow-repeat"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-label">Rata-rata Durasi</div>
                            <div class="metric-value">{{ number_format($summary['average_duration'], 0, ',', '.') }} <span class="fs-6">ms</span></div>
                        </div>
                        <div class="fs-1 text-info opacity-50"><i class="bi bi-speedometer2"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-label">Error Server</div>
                            <div class="metric-value {{ $summary['errors'] > 0 ? 'text-danger' : '' }}">
                                {{ number_format($summary['errors'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="fs-1 text-danger opacity-50"><i class="bi bi-bug"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-label">Kecepatan Pencarian</div>
                    <div class="d-flex justify-content-between mt-2 small">
                        <span class="text-success">Tercepat</span>
                        <span class="fw-semibold">
                            {{ $summary['fastest_search'] !== null ? number_format($summary['fastest_search'], 0, ',', '.').' ms' : '—' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mt-1 small">
                        <span class="text-danger">Terlambat</span>
                        <span class="fw-semibold">
                            {{ $summary['slowest_search'] !== null ? number_format($summary['slowest_search'], 0, ',', '.').' ms' : '—' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">Peringatan Otomatis</span>
                    <span class="badge {{ $unresolvedCount > 0 ? 'text-bg-danger' : 'text-bg-success' }}">
                        {{ $unresolvedCount }} belum selesai
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($alerts->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="fs-1 text-success mb-2"><i class="bi bi-shield-check"></i></div>
                            <p class="text-secondary mb-0">Tidak ada peringatan. Kondisi modul dalam batas normal.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($alerts as $alert)
                                <li class="list-group-item {{ $alert->is_resolved ? 'opacity-75' : '' }}">
                                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                        <div class="flex-grow-1">
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                <span class="badge {{ $alert->severityBadgeClass() }}">{{ $alert->severityLabel() }}</span>
                                                <span class="badge text-bg-light border">{{ $alert->typeLabel() }}</span>
                                                @if($alert->is_resolved)
                                                    <span class="badge text-bg-success">Selesai</span>
                                                @endif
                                                <span class="text-secondary small">{{ $alert->triggered_at?->format('d/m/Y H:i') }}</span>
                                            </div>
                                            <div class="small">{{ $alert->message }}</div>
                                            <div class="text-secondary small mt-1">
                                                Nilai terukur: <strong>{{ rtrim(rtrim((string) $alert->metric_value, '0'), '.') }}</strong>
                                                · Ambang batas: <strong>{{ rtrim(rtrim((string) $alert->threshold, '0'), '.') }}</strong>
                                            </div>
                                        </div>
                                        @if(! $alert->is_resolved)
                                            <form method="POST" action="{{ route('alerts.resolve', $alert) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-check2 me-1"></i>Tandai Selesai
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="card-footer text-secondary small">
                    Pemeriksaan ambang batas dijalankan otomatis setiap 5 menit oleh scheduler.
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><span class="fw-semibold">Ambang Batas Aktif</span></div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-7">Kesalahan server</dt>
                        <dd class="col-5 text-end">&gt; {{ config('monitoring.alerts.error_threshold') }} / {{ config('monitoring.alerts.window_minutes') }} menit</dd>
                        <dt class="col-7">Pencarian lambat</dt>
                        <dd class="col-5 text-end">&gt; {{ config('monitoring.alerts.slow_search_ms') }} ms</dd>
                        <dt class="col-7">Login gagal berturut-turut</dt>
                        <dd class="col-5 text-end">&gt; {{ config('monitoring.alerts.failed_login_threshold') }} kali</dd>
                        <dt class="col-7">Stok menipis</dt>
                        <dd class="col-5 text-end">&le; {{ config('monitoring.low_stock_threshold') }} unit</dd>
                    </dl>
                    <hr>
                    <p class="text-secondary mb-0">
                        Metrik tersimpan di tabel <code>performance_logs</code> dan file log aplikasi untuk analisis teknis.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span class="fw-semibold">Pencatatan Performa Terbaru</span>
            <span class="text-secondary small">{{ $summary['operations'] }} operasi hari ini</span>
        </div>
        <div class="card-body p-0">
            @if($summary['recent']->isEmpty())
                <div class="text-center py-4 text-secondary">Belum ada metrik tercatat hari ini.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Endpoint</th>
                                <th>Method</th>
                                <th class="text-end">Durasi</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Hasil</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary['recent'] as $log)
                                <tr>
                                    <td class="text-nowrap">{{ $log->recorded_at?->format('d/m/Y H:i:s') }}</td>
                                    <td><code>{{ $log->endpoint }}</code></td>
                                    <td>{{ $log->method }}</td>
                                    <td class="text-end">{{ number_format($log->duration_ms, 0, ',', '.') }} ms</td>
                                    <td class="text-center">
                                        <span class="badge {{ $log->statusBadgeClass() }}">{{ $log->status_code }}</span>
                                    </td>
                                    <td class="text-center">{{ $log->result_count !== null ? number_format($log->result_count, 0, ',', '.') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
