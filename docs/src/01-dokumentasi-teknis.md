---
title: "Dokumentasi Teknis — Product Management Service"
subtitle: "Desain, arsitektur, struktur data, dan alur fungsi"
date: "20 November 2026"
lang: id-ID
---

# 1. Ringkasan

Product Management Service adalah modul internal berbasis Laravel 13 untuk mengelola data produk dalam satu tempat. Modul mencakup katalog, pencarian, CRUD dengan soft delete, validasi, autentikasi, catatan aktivitas, pemantauan performa, dan peringatan otomatis.

Dokumen ini menjelaskan desain teknis, arsitektur, struktur data, alur fungsi, konfigurasi, keamanan, serta cara menjalankan dan men-deploy aplikasi.

# 2. Arsitektur

Aplikasi memakai pola MVC berlapis:

```text
Browser (AdminLTE v4 + Bootstrap 5)
        │
        ▼
Routing Laravel 13
        │
        ▼
Middleware web
  - StartSession / CSRF
  - EnsureSessionNotIdle
  - RecordPerformance
        │
        ▼
Route middleware
  - auth
  - role:admin (khusus monitoring)
        │
        ▼
Controller tipis
  - validasi lewat FormRequest
  - memanggil Service
        │
        ▼
Service Layer
  - ProductService
  - ActivityLogService
  - PerformanceLogService
  - AlertService
        │
        ▼
Model Eloquent ──► MySQL 8
        │
        ├──► activity_logs
        ├──► performance_logs
        ├──► alerts
        └──► storage/logs/laravel.log
```

Pemisahan tanggung jawab:

| Lapisan | Tanggung jawab |
|---|---|
| Controller | Menerima request, memanggil service, mengembalikan view/redirect. Tidak berisi logika bisnis berat. |
| FormRequest | Aturan validasi, pesan Bahasa Indonesia, normalisasi input. |
| Service | Logika CRUD, pencarian/filter/urutan, transaksi, pencatatan aktivitas, evaluasi alert. |
| Model | Relasi, cast, scope, soft delete, helper label/badge. |
| Middleware | Idle timeout, otorisasi peran, pencatatan metrik performa. |
| Command + Scheduler | Pemeriksaan ambang batas dan pembuatan alert. |

# 3. Teknologi

| Komponen | Teknologi |
|---|---|
| Bahasa | PHP 8.4 |
| Framework | Laravel 13.43 |
| Database | MySQL 8 |
| Tampilan | Blade + Bootstrap 5 + AdminLTE 4.10 |
| Build frontend | Vite 8, Bootstrap Icons, OverlayScrollbars |
| Autentikasi | Auth Laravel kustom + session database |
| Pengujian | PHPUnit 12 |
| Stress test | k6 2.4 |
| Dokumentasi | Pandoc (Markdown ke .docx) |

# 4. Struktur Folder

```text
app/
├── Console/Commands/
│   ├── CheckAlerts.php
│   └── SeedStressProducts.php
├── Exceptions/
│   └── DuplicateProductException.php
├── Http/
│   ├── Controllers/
│   │   ├── Auth/LoginController.php
│   │   ├── ActivityLogController.php
│   │   ├── AlertController.php
│   │   ├── HelpController.php
│   │   ├── MonitoringController.php
│   │   └── ProductController.php
│   ├── Middleware/
│   │   ├── EnsureSessionNotIdle.php
│   │   ├── EnsureUserRole.php
│   │   └── RecordPerformance.php
│   └── Requests/
│       ├── ActivityLogIndexRequest.php
│       ├── Auth/LoginRequest.php
│       ├── ProductIndexRequest.php
│       ├── StoreProductRequest.php
│       └── UpdateProductRequest.php
├── Models/
│   ├── ActivityLog.php
│   ├── Alert.php
│   ├── Category.php
│   ├── PerformanceLog.php
│   ├── Product.php
│   └── User.php
├── Services/
│   ├── ActivityLogService.php
│   ├── AlertService.php
│   ├── PerformanceLogService.php
│   └── ProductService.php
└── Support/PerformanceMetrics.php
```

# 5. Struktur Data

## 5.1 Tabel `users`

| Field | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Identitas pengguna |
| name | varchar(255) | Nama tampilan |
| email | varchar(255) unique | Login |
| password | varchar(255) | Hash bcrypt |
| role | enum('admin','staff') | Hak akses |
| last_login_at | timestamp nullable | Waktu login terakhir |
| created_at, updated_at | timestamp | Jejak waktu |

## 5.2 Tabel `categories`

| Field | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Identitas kategori |
| name | varchar(100) unique | Nama kategori |
| slug | varchar(120) unique | Kunci URL |
| description | text nullable | Keterangan |
| created_at, updated_at | timestamp | Jejak waktu |

Kategori hanya diisi melalui seeder; CRUD kategori tidak termasuk cakupan.

## 5.3 Tabel `products`

| Field | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Identitas produk |
| sku | varchar(50) unique | Kode produk |
| name | varchar(150) unique | Nama produk |
| category_id | bigint FK | Relasi ke kategori |
| price | decimal(12,2) | Harga, minimal 0 |
| stock | integer | Stok, minimal 0 |
| status | enum('aktif','nonaktif') | Status produk |
| description | text nullable | Deskripsi |
| created_by, updated_by | bigint FK nullable | Pelaku |
| created_at, updated_at | timestamp | Jejak waktu |
| deleted_at | timestamp nullable | Soft delete |

Index tambahan:

- `(deleted_at, created_at)` untuk pengurutan terbaru.
- `(deleted_at, name)` untuk pengurutan nama.
- `(deleted_at, price)` untuk pengurutan harga.
- `(deleted_at, stock)`, `(deleted_at, category_id)`, `(deleted_at, status)` untuk filter dan ringkasan.
- Unique index pada `name` dan `sku`.

Catatan: nama dan SKU tetap unik terhadap produk yang sudah di-soft-delete. Konsekuensi ini disengaja agar penelusuran data tidak ambigu.

## 5.4 Tabel `activity_logs`

| Field | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Identitas catatan |
| user_id | bigint FK nullable | Pelaku |
| product_id | bigint FK nullable | Produk terkait |
| action | enum | create, update, delete, login, logout, search_error |
| description | varchar(255) | Ringkasan |
| old_values | json nullable | Nilai sebelum perubahan |
| new_values | json nullable | Nilai sesudah perubahan |
| ip_address | varchar(45) nullable | IP |
| user_agent | varchar(255) nullable | Perangkat/browser |
| created_at | timestamp | Waktu kejadian |

Login gagal dicatat sebagai `action = login` dengan `new_values.success = false` dan `reason` berisi `invalid_credentials` atau `throttled`. `user_id` boleh null bila email tidak dikenal.

## 5.5 Tabel `performance_logs`

| Field | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Identitas metrik |
| endpoint | varchar(150) | Path yang diukur |
| method | varchar(10) | HTTP method |
| duration_ms | integer | Durasi query/permintaan (ms) |
| status_code | smallint | Status respons |
| result_count | integer nullable | Jumlah hasil pencarian |
| recorded_at | timestamp | Waktu pencatatan |

Index: `recorded_at`, `(endpoint, recorded_at)`, `(status_code, recorded_at)`.

## 5.6 Tabel `alerts`

| Field | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Identitas alert |
| type | varchar(50) | error_rate, slow_search, failed_login |
| severity | enum | info, warning, critical |
| message | varchar(255) | Penjelasan |
| metric_value | decimal(10,2) | Nilai terukur |
| threshold | decimal(10,2) | Ambang batas |
| is_resolved | boolean | Status tindak lanjut |
| triggered_at | timestamp | Waktu muncul |
| resolved_at | timestamp nullable | Waktu selesai |

## 5.7 Tabel `sessions`

Tabel bawaan Laravel dipakai karena `SESSION_DRIVER=database`. Berisi id sesi, user_id, IP, user agent, payload, dan last_activity.

## 5.8 Relasi

```text
users 1 ──── * activity_logs
users 1 ──── * products (created_by)
users 1 ──── * products (updated_by)
users 1 ──── * sessions
categories 1 ──── * products
products 1 ──── * activity_logs
```

# 6. Rute

| Method | URI | Name | Akses | Fungsi |
|---|---|---|---|---|
| GET | `/login` | login | guest | Form login |
| POST | `/login` | login.store | guest | Proses login |
| POST | `/logout` | logout | auth | Logout |
| GET | `/products` | products.index | auth | Katalog, pencarian, filter |
| GET | `/products/create` | products.create | auth | Form tambah |
| POST | `/products` | products.store | auth | Simpan produk |
| GET | `/products/{product}` | products.show | auth | Detail produk |
| GET | `/products/{product}/edit` | products.edit | auth | Form ubah |
| PUT/PATCH | `/products/{product}` | products.update | auth | Perbarui produk |
| DELETE | `/products/{product}` | products.destroy | auth | Soft delete |
| GET | `/activity-logs` | activity-logs.index | auth | Riwayat aktivitas |
| GET | `/activity-logs/{activityLog}` | activity-logs.show | auth | Detail aktivitas |
| GET | `/help` | help.index | auth | Panduan in-app |
| GET | `/monitoring` | monitoring.index | auth + admin | Dashboard performa |
| PATCH | `/alerts/{alert}/resolve` | alerts.resolve | auth + admin | Tandai alert selesai |

# 7. Alur Fungsi

## 7.1 Login

1. `LoginRequest` memvalidasi email dan kata sandi.
2. Rate limiter membatasi 5 percobaan per menit per kombinasi email + IP.
3. `Auth::attempt()` memeriksa kredensial.
4. Jika gagal, aktivitas `login` dengan `success=false` dicatat.
5. Jika berhasil, session diregenerasi, `last_login_at` diperbarui, aktivitas login dicatat, lalu pengguna diarahkan ke katalog.

## 7.2 Tambah Produk

1. `StoreProductRequest` memvalidasi field.
2. `ProductService::create()` membuka transaksi.
3. Service memeriksa duplikat nama/SKU, termasuk produk ter-soft-delete.
4. Jika duplikat, `DuplicateProductException` dilempar; controller mengembalikan error per field disertai tautan ke produk yang sudah ada.
5. Jika unik, produk disimpan dan `ActivityLogService` mencatat snapshot nilai baru.
6. Cache ringkasan katalog dihapus.

## 7.3 Ubah Produk

1. `UpdateProductRequest` memvalidasi field dan mengabaikan produk saat ini pada pengecekan duplikat.
2. Service mengambil snapshot sebelum perubahan.
3. Setelah disimpan, service membandingkan snapshot lama dan baru.
4. Hanya field yang berubah disimpan pada `old_values` dan `new_values`.
5. Jika tidak ada perubahan, tidak ada catatan aktivitas baru.

## 7.4 Hapus Produk

1. Service mengambil snapshot produk.
2. Produk di-soft-delete.
3. Aktivitas `delete` disimpan dengan snapshot lama.
4. Produk hilang dari katalog, tetapi tetap tersedia untuk penelusuran aktivitas.

## 7.5 Pencarian

1. `ProductIndexRequest` memvalidasi `q`, `category`, `status`, dan `sort`.
2. `ProductService::paginate()` menerapkan:
   - `LIKE` pada nama dan SKU dengan escape wildcard.
   - Filter kategori.
   - Filter status `aktif`, `nonaktif`, atau `habis` (stok 0).
   - Urutan `newest`, `name_asc`, `price_asc`, atau `price_desc`.
3. Hasil dipaginasi 15 baris dan mempertahankan query string.
4. Durasi query diukur, ditampilkan di halaman, dan dicatat ke `performance_logs` melalui middleware.

## 7.6 Catatan Aktivitas

Aktivitas dicatat melalui `ActivityLogService`:

- CRUD produk: ditulis di dalam transaksi database bersama perubahan produk.
- Login/logout/gagal login: ditulis oleh `LoginController` dan middleware idle.
- Pencarian gagal: ditulis saat filter tidak valid atau terjadi exception.
- Setiap catatan juga ditulis ke file log aplikasi untuk analisis teknis.

## 7.7 Monitoring dan Alert

Middleware `RecordPerformance` mencatat durasi dan status setiap request pada path yang dikonfigurasi. Command `monitoring:check-alerts` dijalankan scheduler setiap 5 menit:

| Tipe alert | Ambang batas | Severity |
|---|---|---|
| error_rate | > 5 respons 5xx dalam 15 menit | critical |
| slow_search | > 500 ms per pencarian | warning |
| failed_login | > 10 login gagal berturut-turut per email dalam 15 menit | critical |

Alert yang belum selesai tidak diduplikasi; pemeriksaan berikutnya memperbarui alert yang sama. Admin dapat menandai alert selesai dari dashboard.

# 8. Konfigurasi

## 8.1 `config/monitoring.php`

| Kunci | Default | Fungsi |
|---|---|---|
| low_stock_threshold | 10 | Batas stok menipis |
| log_paths | products, activity-logs, monitoring, login, logout | Path yang dicatat |
| alerts.window_minutes | 15 | Jendela evaluasi |
| alerts.error_threshold | 5 | Ambang error 5xx |
| alerts.slow_search_ms | 500 | Ambang pencarian lambat |
| alerts.failed_login_threshold | 10 | Ambang login gagal |

Nilai dapat diubah melalui environment variable terkait (`MONITORING_*`).

## 8.2 Session

- Driver: `database`.
- `SESSION_LIFETIME=120` menit.
- `SESSION_IDLE_TIMEOUT=30` menit.
- Middleware idle memeriksa `auth.last_activity_at`; jika terlampaui, sesi diinvalidasi dan pengguna diarahkan ke login.

## 8.3 Cache

Ringkasan katalog (total, per kategori, stok menipis/habis) disimpan di cache selama 5 menit dan dihapus otomatis setiap ada create/update/delete produk atau seeding. Pada environment `testing`, cache dilewati agar hasil test deterministik.

## 8.4 Scheduler

`routes/console.php`:

```php
Schedule::command('monitoring:check-alerts')
    ->everyFiveMinutes()
    ->withoutOverlapping();
```

Produksi: cron `* * * * * php artisan schedule:run`.

# 9. Keamanan

- Seluruh halaman modul memerlukan login.
- Role `admin` diperlukan untuk monitoring dan resolve alert.
- CSRF aktif pada semua form.
- Password disimpan dengan cast `hashed` (bcrypt).
- Rate limit login 5 percobaan per menit per email + IP.
- Sesi diregenerasi setelah login dan diinvalidasi saat logout/idle.
- Semua input divalidasi di server; output Blade di-escape.
- Mass assignment hanya melalui data tervalidasi.
- Wildcard `%` dan `_` pada pencarian di-escape.
- Log aktivitas tidak memuat kata sandi atau token.
- Direkomendasikan HTTPS dan cookie `Secure` saat produksi.

# 10. Kinerja

Optimasi yang diterapkan:

1. Index pada kolom filter dan pengurutan.
2. Ringkasan katalog memakai satu query agregat + satu query group-by kategori, lalu di-cache.
3. Durasi pencarian diukur di service dan tidak memakai durasi render.
4. Pagination 15 baris.
5. Eager loading relasi untuk menghindari N+1.

Hasil pengukuran pada 10.000 dan 50.000 produk dijelaskan pada `03-laporan-pengujian`.

# 11. Pengujian

- 53 test PHPUnit, 171 assertion.
- `DatabaseTruncation` dipakai agar setiap test mendapat database bersih tanpa transaksi yang dapat mengganggu index/constraint tertentu.
- Test memakai database MySQL `product_m_test`.
- `tests/TestCase.php` menolak berjalan bila `bootstrap/cache/config.php` masih ada, untuk mencegah test menyentuh database pengembangan.
- Stress test memakai k6 pada `tests/Stress/search.js`.

# 12. Deployment

## 12.1 Persiapan

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --force
php artisan optimize
```

## 12.2 Nginx + PHP-FPM

Contoh konfigurasi:

```nginx
server {
    listen 80;
    server_name produk.internal;
    root /var/www/product-management/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Pastikan direktori `storage` dan `bootstrap/cache` dapat ditulis oleh user PHP-FPM.

## 12.3 Operasional

- Jalankan `php artisan schedule:work` atau cron scheduler.
- Pantau `storage/logs/laravel.log` dan dashboard monitoring.
- Backup database secara berkala.
- Bersihkan `performance_logs` lama bila volume bertambah (belum ada command pruning otomatis).

# 13. Batasan dan Keputusan

- Kategori hanya di-seed; CRUD kategori di luar cakupan.
- Manajemen pengguna dan reset password tidak termasuk.
- Produk terhapus tidak dapat dipulihkan dari UI.
- Notifikasi alert hanya di dashboard, belum melalui email.
- Alert error_rate menghitung respons 5xx; validasi 4xx tidak dianggap kesalahan server.
- Pencarian memakai `LIKE`; uji coba fulltext tidak dipertahankan karena lebih lambat untuk kata yang cocok dengan hampir seluruh baris.
- Pengujian sistem dilakukan pada level HTTP (feature test), bukan browser automation.
