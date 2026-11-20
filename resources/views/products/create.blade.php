@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Katalog</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tambah</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <span class="fw-semibold">Formulir Produk Baru</span>
                </div>
                <div class="card-body">
                    @include('products._form', [
                        'product' => null,
                        'categories' => $categories,
                        'action' => route('products.store'),
                        'method' => 'POST',
                    ])
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><span class="fw-semibold">Panduan Singkat</span></div>
                <div class="card-body text-secondary small">
                    <ul class="mb-0 ps-3">
                        <li>Nama dan SKU harus unik.</li>
                        <li>Harga dan stok tidak boleh negatif.</li>
                        <li>Deskripsi bersifat opsional.</li>
                        <li>Produk akan langsung tampil di katalog setelah disimpan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
