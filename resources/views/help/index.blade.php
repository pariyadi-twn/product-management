@extends('layouts.app')

@section('title', 'Bantuan & Panduan')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Bantuan</li>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><span class="fw-semibold">Panduan Pengguna Singkat</span></div>
                <div class="card-body">
                    <h2 class="h5">1. Masuk ke aplikasi</h2>
                    <p class="text-secondary">
                        Buka alamat aplikasi, lalu masukkan email dan kata sandi kantor Anda. Setelah berhasil, Anda akan diarahkan ke Katalog Produk.
                        Sesi berakhir otomatis bila aplikasi dibiarkan tanpa aktivitas selama batas waktu yang ditentukan.
                    </p>

                    <h2 class="h5 mt-4">2. Melihat dan mencari produk</h2>
                    <ul class="text-secondary">
                        <li>Katalog menampilkan ringkasan jumlah produk, kategori, stok menipis, dan stok habis.</li>
                        <li>Ketik nama atau SKU pada kotak pencarian untuk mempersempit hasil.</li>
                        <li>Gunakan filter kategori dan status, serta pilihan urutan sesuai kebutuhan.</li>
                        <li>Klik nama produk untuk membuka detail dalam mode baca.</li>
                    </ul>

                    <h2 class="h5 mt-4">3. Menambah produk</h2>
                    <ol class="text-secondary">
                        <li>Klik <strong>Tambah Produk</strong>.</li>
                        <li>Isi nama, SKU, kategori, harga, stok, status, dan deskripsi bila perlu.</li>
                        <li>Klik <strong>Simpan</strong>. Bila ada isian yang salah, pesan akan muncul di bawah isian terkait.</li>
                        <li>Nama dan SKU harus unik. Bila sudah digunakan, aplikasi menampilkan tautan ke produk yang sudah ada.</li>
                    </ol>

                    <h2 class="h5 mt-4">4. Mengubah atau menghapus produk</h2>
                    <ul class="text-secondary">
                        <li>Dari katalog atau detail, klik <strong>Ubah</strong> untuk memperbarui data. Nilai lama dan baru dicatat pada catatan aktivitas.</li>
                        <li>Klik <strong>Hapus</strong>, lalu konfirmasi pada dialog. Produk dihapus dari katalog, tetapi datanya tetap tersimpan untuk penelusuran.</li>
                    </ul>

                    <h2 class="h5 mt-4">5. Menelusuri catatan aktivitas</h2>
                    <p class="text-secondary">
                        Buka menu <strong>Catatan Aktivitas</strong> untuk melihat riwayat tambah, ubah, hapus, masuk, dan keluar.
                        Saring berdasarkan jenis aktivitas, produk, atau rentang tanggal. Klik ikon mata untuk melihat rincian perubahan.
                    </p>

                    <h2 class="h5 mt-4">6. Memantau performa (khusus admin)</h2>
                    <p class="text-secondary">
                        Menu <strong>Pantau Performa</strong> menampilkan jumlah operasi, rata-rata durasi, jumlah error server, kecepatan pencarian,
                        dan daftar peringatan otomatis. Setelah ditangani, tandai peringatan sebagai selesai.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><span class="fw-semibold">Pintasan</span></div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-box-seam me-2"></i>Katalog Produk
                    </a>
                    <a href="{{ route('products.create') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-plus-lg me-2"></i>Tambah Produk
                    </a>
                    <a href="{{ route('activity-logs.index') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-clock-history me-2"></i>Catatan Aktivitas
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('monitoring.index') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-activity me-2"></i>Pantau Performa
                        </a>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><span class="fw-semibold">Pertanyaan Umum</span></div>
                <div class="card-body small text-secondary">
                    <p class="fw-semibold text-body mb-1">Mengapa produk tidak bisa disimpan?</p>
                    <p>Periksa pesan kesalahan pada isian. Pastikan nama/SKU belum digunakan dan harga/stok tidak negatif.</p>

                    <p class="fw-semibold text-body mb-1">Mengapa saya diminta masuk ulang?</p>
                    <p>Sesi berakhir otomatis setelah tidak ada aktivitas dalam batas waktu. Masuk kembali dengan akun Anda.</p>

                    <p class="fw-semibold text-body mb-1">Apakah produk yang dihapus bisa dipulihkan?</p>
                    <p>Produk dihapus dari katalog dan belum tersedia fitur pemulihan pada prototype ini. Riwayat perubahannya tetap tercatat.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
