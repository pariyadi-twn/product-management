---
title: "Panduan Pengguna — Product Management Service"
subtitle: "Langkah demi langkah mengelola data produk"
date: "20 November 2026"
lang: id-ID
---

# 1. Tentang Aplikasi

Product Management Service adalah aplikasi internal untuk menyimpan dan mengelola data produk. Semua produk tampil dalam satu katalog yang dapat dicari, difilter, dan diurutkan. Setiap perubahan tercatat sehingga riwayat data dapat ditelusuri.

Aplikasi ini digunakan melalui browser dan memerlukan login.

# 2. Masuk ke Aplikasi

1. Buka alamat aplikasi yang diberikan oleh tim teknis.
2. Masukkan **email** dan **kata sandi** kantor Anda.
3. Klik **Masuk**.

Akun demo untuk lingkungan lokal:

| Peran | Email | Kata Sandi |
|---|---|---|
| Admin | admin@example.com | password |
| Staff | staff@example.com | password |

Jika email atau kata sandi salah, pesan kesalahan akan muncul. Setelah 5 percobaan gagal dalam satu menit, login akan ditahan sementara.

# 3. Mengenal Halaman Katalog

Setelah masuk, Anda langsung diarahkan ke **Katalog Produk**. Halaman ini berisi:

- **Kartu ringkasan**: total produk, jumlah kategori, stok menipis, dan stok habis.
- **Ringkasan per kategori**: jumlah produk pada setiap kategori.
- **Pencarian dan filter**: kata kunci, kategori, status, dan urutan.
- **Tabel produk**: SKU, nama, kategori, harga, stok, status, dan tombol aksi.
- **Pagination**: berpindah halaman tanpa kehilangan filter.

## 3.1 Mencari Produk

1. Ketik potongan nama atau SKU pada kotak **Cari nama atau SKU**.
2. Tekan **Enter** atau klik **Cari**.

Hasil yang cocok akan tampil. Di atas tabel terlihat jumlah hasil dan durasi pencarian.

Jika tidak ada hasil, aplikasi menampilkan penjelasan dan tombol **Hapus Semua Filter**. Coba gunakan kata kunci yang lebih pendek atau hapus filter yang aktif.

## 3.2 Memfilter dan Mengurutkan

- **Kategori**: pilih satu kategori atau biarkan kosong untuk semua kategori.
- **Status**: pilih Aktif, Nonaktif, atau Stok habis.
- **Urutkan**: Terbaru, Nama (A-Z), Harga terendah, atau Harga tertinggi.

Filter kategori, status, dan urutan langsung diterapkan setelah dipilih. Tombol **X** di samping tombol Cari menghapus semua filter.

## 3.3 Melihat Detail Produk

Klik nama produk pada tabel untuk membuka **Detail Produk**. Halaman ini menampilkan informasi lengkap dalam mode baca, termasuk siapa yang membuat dan terakhir mengubah data.

Dari halaman detail Anda dapat:

- Klik **Ubah** untuk memperbarui produk.
- Klik **Hapus** untuk menghapus produk.
- Klik **Kembali ke Katalog**.

# 4. Menambah Produk

1. Dari katalog, klik tombol **Tambah Produk**.
2. Isi formulir:
   - **Nama Produk** (wajib, maksimal 150 karakter)
   - **SKU** (wajib, maksimal 50 karakter)
   - **Kategori** (wajib)
   - **Harga** (wajib, tidak boleh negatif)
   - **Stok** (wajib, tidak boleh negatif)
   - **Status** (Aktif atau Nonaktif)
   - **Deskripsi** (opsional, maksimal 5.000 karakter)
3. Klik **Simpan**.

Jika ada isian yang salah, pesan muncul tepat di bawah isian tersebut dan data tidak tersimpan.

## 4.1 Data Ganda

Nama produk dan SKU harus unik. Jika Anda memasukkan nama atau SKU yang sudah dipakai, aplikasi akan:

- Menampilkan pesan pada isian yang bermasalah.
- Menampilkan tautan ke produk yang sudah ada.

Klik tautan tersebut untuk memeriksa produk lama sebelum melanjutkan.

# 5. Mengubah Produk

1. Dari katalog atau halaman detail, klik **Ubah**.
2. Formulir akan terisi data lama.
3. Ubah nilai yang diperlukan.
4. Klik **Simpan**.

Nilai sebelum dan sesudah perubahan dicatat pada **Catatan Aktivitas**. Jika tidak ada nilai yang berubah, tidak ada catatan perubahan baru.

# 6. Menghapus Produk

1. Klik tombol **Hapus** pada baris produk atau halaman detail.
2. Dialog konfirmasi akan muncul: "Yakin ingin menghapus produk ...?"
3. Klik **Hapus Produk** untuk melanjutkan atau **Batal** untuk membatalkan.

Produk akan hilang dari katalog. Data tetap tersimpan untuk keperluan penelusuran aktivitas dan belum tersedia fitur pemulihan pada versi ini.

# 7. Melihat Catatan Aktivitas

1. Buka menu **Catatan Aktivitas** pada sidebar.
2. Riwayat terbaru tampil dalam tabel: waktu, jenis, deskripsi, produk, pelaku, dan IP.
3. Saring riwayat dengan:
   - **Jenis aktivitas**: Tambah produk, Ubah produk, Hapus produk, Masuk, Keluar, atau Pencarian gagal.
   - **Produk**: ketik nama atau SKU produk terkait.
   - **Rentang tanggal**: isi tanggal mulai dan/atau tanggal selesai.
4. Klik ikon mata untuk membuka detail aktivitas.

Detail aktivitas menampilkan nilai lama dan nilai baru untuk setiap isian yang berubah. Untuk aktivitas login/logout, ditampilkan informasi email, alasan, atau detail percobaan.

# 8. Memantau Performa (Khusus Admin)

Menu **Pantau Performa** hanya tersedia untuk admin.

Halaman ini menampilkan:

- Jumlah operasi hari ini.
- Rata-rata durasi permintaan.
- Jumlah error server.
- Pencarian tercepat dan terlambat.
- Daftar peringatan otomatis.
- Tabel pencatatan performa terbaru.

## 8.1 Menindaklanjuti Peringatan

Peringatan muncul saat kondisi berikut terdeteksi:

- Lebih dari 5 error server dalam 15 menit.
- Pencarian lebih dari 500 ms.
- Lebih dari 10 login gagal berturut-turut untuk email yang sama dalam 15 menit.

Setelah masalah ditangani, klik **Tandai Selesai** pada peringatan terkait. Peringatan yang sudah selesai tetap terlihat dengan status **Selesai**.

# 9. Bantuan

Menu **Bantuan** berisi ringkasan panduan, pintasan ke halaman penting, dan pertanyaan umum. Panduan lengkap juga tersedia pada dokumen ini.

# 10. Keluar dan Sesi Berakhir

- Klik nama Anda di kanan atas, lalu pilih **Keluar**.
- Sesi juga berakhir otomatis setelah 30 menit tanpa aktivitas.
- Jika sesi berakhir, aplikasi akan meminta Anda masuk kembali.

Selalu keluar setelah menggunakan komputer bersama.

# 11. Pesan dan Masalah Umum

| Situasi | Yang perlu dilakukan |
|---|---|
| Nama atau SKU sudah digunakan | Klik tautan produk yang sudah ada, lalu gunakan nama/SKU lain. |
| Harga atau stok tidak valid | Isi angka 0 atau lebih besar; jangan gunakan huruf. |
| Kategori belum dipilih | Pilih kategori dari daftar. |
| Produk tidak ditemukan | Perlebar kata kunci atau hapus filter. |
| Harus login ulang | Sesi berakhir karena idle; masuk kembali. |
| Tidak bisa membuka Pantau Performa | Halaman hanya untuk admin. Hubungi admin bila perlu akses. |
| Peringatan tidak hilang | Tandai selesai setelah masalah ditangani; jika berulang, laporkan ke tim teknis. |

# 12. Tips Penggunaan

- Gunakan SKU yang konsisten, misalnya `ELK-001`, `PKN-001`.
- Periksa kategori sebelum menyimpan agar filter katalog akurat.
- Gunakan deskripsi untuk informasi tambahan yang tidak muat pada nama.
- Gunakan filter **Stok habis** atau **Stok menipis** untuk perencanaan restock.
- Periksa **Catatan Aktivitas** bila ada data yang tidak sesuai.
