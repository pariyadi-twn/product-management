@extends('layouts.app')

@section('title', 'Detail Produk')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Katalog</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <span class="fw-semibold">{{ $product->name }}</span>
                        <code class="ms-2">{{ $product->sku }}</code>
                    </div>
                    <div class="d-flex gap-1">
                        <span class="badge {{ $product->statusBadgeClass() }}">{{ $product->statusLabel() }}</span>
                        <span class="badge {{ $product->stockBadgeClass() }}">Stok: {{ $product->stock }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Kategori</dt>
                        <dd class="col-sm-8">{{ $product->category->name }}</dd>

                        <dt class="col-sm-4">Harga</dt>
                        <dd class="col-sm-8">{{ $product->formattedPrice() }}</dd>

                        <dt class="col-sm-4">Stok</dt>
                        <dd class="col-sm-8">
                            {{ $product->stock }} unit
                            <span class="badge {{ $product->stockBadgeClass() }} ms-1">{{ $product->stockLabel() }}</span>
                        </dd>

                        <dt class="col-sm-4">Deskripsi</dt>
                        <dd class="col-sm-8 product-description">{{ $product->description ?: '—' }}</dd>
                    </dl>
                </div>
                <div class="card-footer d-flex flex-wrap gap-2">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Ubah
                    </a>
                    <button
                        type="button"
                        class="btn btn-outline-danger btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteProductModal"
                        data-delete-action="{{ route('products.destroy', $product) }}"
                        data-delete-name="{{ $product->name }}"
                    >
                        <i class="bi bi-trash me-1"></i>Hapus
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm ms-auto">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Katalog
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><span class="fw-semibold">Jejak Data</span></div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-5">Dibuat oleh</dt>
                        <dd class="col-7">{{ $product->createdBy?->name ?? '—' }}</dd>

                        <dt class="col-5">Dibuat pada</dt>
                        <dd class="col-7">{{ $product->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>

                        <dt class="col-5">Diubah oleh</dt>
                        <dd class="col-7">{{ $product->updatedBy?->name ?? '—' }}</dd>

                        <dt class="col-5">Diubah pada</dt>
                        <dd class="col-7">{{ $product->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    @include('products._delete-modal')
@endsection
