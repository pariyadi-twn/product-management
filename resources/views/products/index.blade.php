@extends('layouts.app')

@section('title', 'Katalog Produk')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Katalog</li>
@endsection

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-label">Total Produk</div>
                            <div class="metric-value">{{ number_format($summary['total'], 0, ',', '.') }}</div>
                        </div>
                        <div class="fs-1 text-primary opacity-50"><i class="bi bi-box-seam"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-label">Kategori</div>
                            <div class="metric-value">{{ number_format($summary['categories'], 0, ',', '.') }}</div>
                        </div>
                        <div class="fs-1 text-info opacity-50"><i class="bi bi-tags"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-label">Stok Menipis</div>
                            <div class="metric-value">{{ number_format($summary['low_stock'], 0, ',', '.') }}</div>
                        </div>
                        <div class="fs-1 text-warning opacity-50"><i class="bi bi-exclamation-triangle"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="metric-label">Stok Habis</div>
                            <div class="metric-value">{{ number_format($summary['out_of_stock'], 0, ',', '.') }}</div>
                        </div>
                        <div class="fs-1 text-danger opacity-50"><i class="bi bi-x-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-2">
            <span class="text-secondary small me-1">Produk per kategori:</span>
            @foreach($summary['per_category'] as $category)
                <span class="badge text-bg-light border">
                    {{ $category->name }}
                    <span class="text-secondary">({{ $category->products_count }})</span>
                </span>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span class="fw-semibold">Daftar Produk</span>
            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Tambah Produk
            </a>
        </div>
        <div class="card-body p-0">
            @if($products->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="fs-1 text-secondary mb-2"><i class="bi bi-inbox"></i></div>
                    <h2 class="h5">Belum ada produk</h2>
                    <p class="text-secondary mb-3">Mulai dengan menambahkan produk pertama Anda.</p>
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Produk
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>SKU</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th class="text-end">Harga</th>
                                <th class="text-center">Stok</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td><code>{{ $product->sku }}</code></td>
                                    <td>
                                        <a href="{{ route('products.show', $product) }}" class="fw-semibold text-decoration-none">
                                            {{ $product->name }}
                                        </a>
                                    </td>
                                    <td>{{ $product->category->name }}</td>
                                    <td class="text-end">{{ $product->formattedPrice() }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $product->stockBadgeClass() }}">{{ $product->stock }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $product->statusBadgeClass() }}">{{ $product->statusLabel() }}</span>
                                    </td>
                                    <td class="text-end table-actions">
                                        <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary btn-sm" title="Detail" data-bs-toggle="tooltip">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary btn-sm" title="Ubah" data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button
                                            type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            title="Hapus"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteProductModal"
                                            data-delete-action="{{ route('products.destroy', $product) }}"
                                            data-delete-name="{{ $product->name }}"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($products->hasPages())
            <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="text-secondary small">
                    Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari {{ $products->total() }} produk
                </div>
                {{ $products->links() }}
            </div>
        @endif
    </div>

    @include('products._delete-modal')
@endsection
