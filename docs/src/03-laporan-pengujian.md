---
title: "Laporan Pengujian — Product Management Service"
subtitle: "Pengujian integrasi, sistem, dan stress"
date: "20 November 2026"
lang: id-ID
---

# 1. Ringkasan Eksekutif

Pengujian dilakukan pada tiga level:

1. **Integrasi**: CRUD, validasi, pencarian, filter, otorisasi, aktivitas, monitoring, dan alert.
2. **Sistem**: alur end-to-end login → tambah → cari → ubah → hapus → catatan aktivitas → monitoring → logout.
3. **Stress**: pencarian dan katalog pada 10.000 dan 50.000 produk menggunakan k6, termasuk permintaan paralel.

Hasil utama:

| Metrik | Target PRD | Hasil |
|---|---|---|
| Automated test | Semua lulus | 53 test, 171 assertion, 100% lulus |
| Pencarian 10.000 baris | < 300 ms | p95 90 ms (20 permintaan berurutan) |
| Halaman katalog 10.000 baris | < 2 detik | p95 0,859 detik (20 permintaan berurutan) |
| Pencarian 50.000 baris | Diuji sebagai stress | p95 228 ms (20 permintaan berurutan) |
| Halaman katalog 50.000 baris | Diuji sebagai stress | p95 0,918 detik (20 permintaan berurutan) |
| Error rate stress | < 1% | 0% pada seluruh skenario k6 |

Pada pengujian paralel dengan host bersama yang sedang sibuk, latency meningkat. Detail dan analisis ada pada Bagian 6 dan 7.

# 2. Lingkungan Pengujian

| Komponen | Versi/Keterangan |
|---|---|
| PHP | 8.4.28 |
| Laravel | 13.43.0 |
| Database | MySQL 8 |
| Database test | `product_m_test` |
| Web server (stress) | Nginx 1.24 + PHP-FPM |
| Node.js | 26.5 |
| Build frontend | Vite 8, AdminLTE 4.10.2 |
| Test runner | PHPUnit 12.5 |
| Load generator | k6 v2.4.0 |
| OS | Linux (Ubuntu 24.04) |
| Kondisi host | Shared host; Chrome, Zoom, dan VS Code aktif; load average 7–20 selama sebagian pengujian |
| Konfigurasi aplikasi | `APP_DEBUG=false`, config/route/view cache aktif, OPcache PHP-FPM aktif |

Catatan: pengujian stress dijalankan di mesin yang sama dengan browser, aplikasi meeting, dan editor kode. Angka latency dipengaruhi beban host.

# 3. Cakupan Pengujian Integrasi

Perintah:

```bash
php artisan config:clear
php artisan test
```

Hasil:

```text
53 tests passed
171 assertions
```

| Kelompok | Jumlah test | Cakupan |
|---|---:|---|
| Autentikasi | 6 | Login valid/gagal, throttle, logout, last_login_at |
| Manajemen produk | 7 | Katalog, detail, create, update, soft delete, validasi, duplikat |
| Pencarian dan filter | 9 | Nama, SKU, kategori, status, stok habis, urutan, empty state, wildcard, pagination |
| Catatan aktivitas | 9 | Create/update/delete, login/logout, gagal login, search error, filter, detail diff |
| Idle timeout | 3 | Sesi berakhir, sesi aktif, refresh waktu aktivitas |
| Monitoring | 6 | Performance log, dashboard admin, akses staff, resolve alert |
| Alert | 5 | Error rate, di bawah ambang, slow search, failed login, deduplikasi |
| Stress seeder | 2 | Seed, clean, idempotensi |
| Role access | 4 | Guest, staff, admin |
| Unit | 2 | Perbandingan nilai lama/baru pada service |
| Total | 53 | — |

# 4. Pengujian Sistem

Skenario end-to-end yang diuji:

1. Guest membuka katalog → diarahkan ke login.
2. Login admin berhasil → diarahkan ke katalog → aktivitas login tercatat.
3. Tambah produk baru → produk tampil → aktivitas create dengan snapshot baru.
4. Cari produk berdasarkan nama/SKU → hasil muncul; filter dan urutan bekerja.
5. Ubah produk → nilai lama dan baru tercatat; pelaku dan waktu benar.
6. Hapus produk → produk hilang dari katalog; soft delete dan aktivitas delete tercatat.
7. Buka catatan aktivitas → filter dan detail diff sesuai.
8. Buka monitoring → metrik performa dan alert tampil.
9. Resolve alert → status berubah menjadi selesai.
10. Logout → sesi berakhir dan aktivitas logout tercatat.

Seluruh skenario lulus pada automated test. Pengujian sistem dilakukan pada level HTTP (feature test), bukan browser automation.

# 5. Skenario Stress Test

Alat dan konfigurasi:

- k6 v2.4.0.
- Skenario `search`: 3 VU (baseline) dan 10 VU (stress), 20–30 detik.
- Skenario `catalog`: 1 VU (baseline) dan 5 VU (stress).
- Data: 10.000 dan 50.000 produk stress dengan prefix `STR-`.
- Login dilakukan sekali di `setup()`, cookie sesi dibagikan ke seluruh VU.
- Threshold k6:
  - `search_duration_ms` p95 < 300 ms.
  - HTTP katalog p95 < 2.000 ms.
  - `http_req_failed` < 1%.

Durasi pencarian diambil dari header `X-Search-Duration-Ms`, yang berasal dari pengukuran query di `ProductService`. Ini memisahkan durasi pencarian dari waktu render halaman.

Berkas bukti:

- `docs/stress/sequential-10k.txt`
- `docs/stress/sequential-50k.txt`
- `docs/stress/k6-10k-3vus.txt`
- `docs/stress/k6-10k-15vus.txt`
- `docs/stress/k6-50k-3vus.txt`

# 6. Hasil Pengujian

## 6.1 Permintaan Berurutan (20 permintaan)

| Dataset | Rata-rata HTTP | p95 HTTP | Rata-rata pencarian | p95 pencarian | Maks pencarian |
|---|---:|---:|---:|---:|---:|
| 10.000 produk | 0,324 s | 0,859 s | 68 ms | 90 ms | 102 ms |
| 50.000 produk | 0,427 s | 0,918 s | 190 ms | 228 ms | 232 ms |

Kedua dataset memenuhi target PRD untuk pencarian < 300 ms dan halaman katalog < 2 detik pada kondisi normal.

## 6.2 Parallel 10.000 Produk — 3 VU pencarian + 1 VU katalog

| Metrik | Hasil |
|---|---|
| Total request | 111 |
| Throughput | 4,55 request/detik |
| Error rate | 0% |
| Pencarian p50 | 114 ms |
| Pencarian p90 | 289 ms |
| Pencarian p95 | 307 ms |
| HTTP p95 | 1,26 s |
| Katalog p95 | 1,30 s |

Pencarian p95 sedikit di atas target 300 ms. Penyebab utama adalah beban host bersama, bukan query plan.

## 6.3 Parallel 10.000 Produk — 10 VU pencarian + 5 VU katalog

| Metrik | Hasil |
|---|---|
| Total request | 235 |
| Throughput | 6,70 request/detik |
| Error rate | 0% |
| Pencarian p50 | 324 ms |
| Pencarian p95 | 752 ms |
| HTTP p95 | 2,95 s |
| Katalog p95 | 2,64 s |

Pada 15 VU, latency query dan halaman melewati target. Tidak ada request gagal.

## 6.4 Parallel 50.000 Produk — 3 VU pencarian + 1 VU katalog

| Metrik | Hasil |
|---|---|
| Total request | 59 |
| Throughput | 2,31 request/detik |
| Error rate | 0% |
| Pencarian p50 | 1.194 ms |
| Pencarian p95 | 1.497 ms |
| HTTP p95 | 2,18 s |
| Katalog p95 | 1,24 s |

Pada 50.000 produk dengan permintaan paralel, pencarian melewati target. Query masih berada di bawah 300 ms saat berurutan (p95 228 ms), tetapi kontensi CPU/IO pada host bersama meningkatkan latency.

# 7. Temuan dan Perbaikan

## 7.1 Ringkasan katalog terlalu banyak query

**Temuan:** ringkasan awal menjalankan lima query terpisah (total, kategori, stok menipis, stok habis, per kategori) dan memakan sekitar 238 ms pada 10.000 produk.

**Perbaikan:**

- Digabung menjadi satu query agregat untuk total/stok.
- Satu query group-by untuk jumlah per kategori.
- Hasil di-cache 5 menit dan dihapus otomatis saat produk berubah.
- Pada environment test, cache dilewati agar deterministik.

**Hasil:** cache hit hanya membaca satu entri cache; query agregat tidak lagi dijalankan pada setiap request.

## 7.2 Pengurutan katalog menyebabkan filesort pada data besar

**Temuan:** `ORDER BY created_at DESC LIMIT 15` pada 50.000 baris melakukan filesort. Pengukuran awal menunjukkan sekitar 1.469 ms untuk select, sehingga total pencarian mencapai sekitar 2,6 detik pada uji berurutan.

**Perbaikan:** menambahkan composite index:

- `(deleted_at, created_at)`
- `(deleted_at, name)`
- `(deleted_at, price)`
- `(deleted_at, stock)`
- `(deleted_at, category_id)`
- `(deleted_at, status)`

**Hasil:** select turun menjadi sekitar 9 ms; pencarian 50.000 produk berurutan turun dari rata-rata 757 ms menjadi 190 ms (p95 228 ms).

## 7.3 Uji coba fulltext tidak dipertahankan

**Temuan:** fulltext index diuji untuk mempercepat pencarian. Pada kata kunci yang cocok dengan hampir seluruh 50.000 baris (mis. "Stress"), fulltext justru lebih lambat (sekitar 1.181 ms untuk count dan 1.469 ms untuk select) dibanding `LIKE` (sekitar 196 ms untuk count).

**Keputusan:** fulltext dihapus; pencarian memakai `LIKE` dengan index pengurutan dan escape wildcard. Untuk skala data yang jauh lebih besar, disarankan search engine khusus (mis. Meilisearch/Elasticsearch).

## 7.4 Sesi k6 hilang antar iterasi

**Temuan:** k6 menghapus cookie di antara iterasi secara default. Akibatnya VU kehilangan sesi login dan menerima halaman login, sehingga hasil pengukuran tidak valid.

**Perbaikan:** login dilakukan sekali di `setup()`, cookie sesi dibagikan ke setiap VU dan diterapkan sebelum request. Ini juga menghindari rate limit login karena seluruh VU berasal dari IP yang sama.

## 7.5 Config cache membuat test memakai database pengembangan

**Temuan:** saat `bootstrap/cache/config.php` aktif, konfigurasi `phpunit.xml` diabaikan. `RefreshDatabase` dapat menjalankan `migrate:fresh` pada database pengembangan.

**Perbaikan:**

- Test memakai `DatabaseTruncation` dan database `product_m_test`.
- `tests/TestCase.php` menolak berjalan jika config cache masih ada.
- Prosedur pengujian selalu menjalankan `php artisan config:clear` sebelum test.
- Data demo dipulihkan dengan `php artisan db:seed` setelah insiden.

## 7.6 Rate limit login pada pengujian paralel

**Temuan:** seluruh VU k6 berbagi IP dan email, sehingga login paralel terkena rate limit 5 percobaan per menit.

**Perbaikan:** satu sesi dibuat di `setup()` dan cookie dibagikan; rate limit tetap aktif untuk pengguna sebenarnya.

# 8. Analisis

1. **Target normal terpenuhi.** Pada 10.000 produk, pencarian p95 90 ms dan halaman katalog p95 0,859 detik. Bahkan pada 50.000 produk, pencarian berurutan p95 228 ms.
2. **Bottleneck paralel adalah sumber daya host.** Dengan 15 VU pada host yang juga menjalankan Chrome, Zoom, dan editor, throughput hanya sekitar 6,7 request/detik dan latency naik. Error rate tetap 0%.
3. **Optimasi query berhasil.** Index pengurutan menurunkan waktu select 50.000 produk dari sekitar 1,5 detik menjadi sekitar 9 ms.
4. **Query pencarian `LIKE` tetap full scan.** Untuk 10.000–50.000 baris hasilnya masih memenuhi target saat berurutan, tetapi tidak cocok untuk jutaan baris.
5. **Monitoring berfungsi.** Metrik tersimpan di `performance_logs`; pencarian lambat > 500 ms memicu log peringatan dan dapat memicu alert melalui command.

# 9. Rekomendasi

- Jalankan di server terpisah dari browser/meeting/editor untuk hasil stress yang stabil.
- Gunakan PHP-FPM dengan jumlah worker sesuai CPU dan memory; aktifkan OPcache.
- Pertimbangkan fulltext/search engine khusus jika data melewati ratusan ribu baris atau pencarian paralel menjadi beban utama.
- Tambahkan command pruning untuk `performance_logs` lama.
- Tambahkan notifikasi email untuk alert critical bila diperlukan.
- Pertimbangkan pagination berbasis cursor untuk katalog yang sangat besar.

# 10. Kesimpulan

Implementasi memenuhi kebutuhan fungsional PRD dan target performa pada kondisi normal untuk 10.000 baris data. Seluruh 53 automated test lulus tanpa kegagalan. Stress test menunjukkan aplikasi tetap stabil tanpa error hingga 15 VU, meskipun latency pada host bersama melewati target. Temuan utama telah diperbaiki, terutama index pengurutan, query ringkasan, dan mekanisme pengujian sesi. Rekomendasi lanjutan berfokus pada kapasitas server dan strategi pencarian untuk skala data yang lebih besar.
