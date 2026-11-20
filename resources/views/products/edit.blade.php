@extends('layouts.app')

@section('title', 'Ubah Produk')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Katalog</a></li>
    <li class="breadcrumb-item"><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Ubah</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <span class="fw-semibold">Formulir Ubah Produk</span>
                </div>
                <div class="card-body">
                    @include('products._form', [
                        'product' => $product,
                        'categories' => $categories,
                        'action' => route('products.update', $product),
                        'method' => 'PUT',
                    ])
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><span class="fw-semibold">Informasi</span></div>
                <div class="card-body text-secondary small">
                    Perubahan tersimpan setelah formulir lolos validasi. Nilai sebelum dan sesudah perubahan akan dicatat pada catatan aktivitas.
                </div>
            </div>
        </div>
    </div>
@endsection
