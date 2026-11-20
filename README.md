# Product Management Service

Modul internal berbasis Laravel 13 untuk mengelola data produk: katalog, pencarian, CRUD, catatan aktivitas, pemantauan performa, dan peringatan otomatis.

## Fitur

- Login/logout, rate limit, idle timeout, dan pembatasan peran `admin`/`staff`.
- Katalog produk dengan ringkasan jumlah, pencarian nama/SKU, filter kategori/status, dan pengurutan.
- CRUD produk dengan validasi server, validasi tampilan, pencegahan nama/SKU ganda, dan soft delete.
- Catatan aktivitas: tambah, ubah, hapus, masuk, keluar, dan pencarian gagal; lengkap dengan nilai lama/baru.
- Pemantauan performa: durasi permintaan, jumlah error, kecepatan pencarian, dan pencarian terbaru.
- Peringatan otomatis untuk error rate, pencarian lambat, dan login gagal berulang.
- Halaman bantuan in-app dan dokumentasi `.docx` di folder `docs`.

## Teknologi

- PHP 8.3+ (diuji pada PHP 8.4)
- Laravel 13
- MySQL 8
- Bootstrap 5 + AdminLTE 4 + Vite
- PHPUnit 12

## Persyaratan

- PHP 8.3+ dengan ekstensi `pdo_mysql`, `mbstring`, `xml`, `curl`
- Composer
- Node.js 18+ dan npm
- MySQL 8

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database pada `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=product-m
DB_USERNAME=root
DB_PASSWORD=
```

Buat database jika belum ada, lalu jalankan migrasi dan seeder:

```bash
mysql -u root -p -e "CREATE DATABASE \`product-m\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
```

Instal dan build aset frontend:

```bash
npm install
npm run build
```

Jalankan aplikasi:

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Akun Demo

| Peran | Email | Kata Sandi |
|---|---|---|
| Admin | `admin@example.com` | `password` |
| Staff | `staff@example.com` | `password` |

Akun dibuat oleh `UserSeeder`. Ganti kata sandi sebelum dipakai di luar lingkungan lokal.

## Peran Pengguna

- **Admin**: seluruh fitur produk dan aktivitas, membuka Pantau Performa, dan menandai peringatan selesai.
- **Staff**: katalog, pencarian, CRUD produk, catatan aktivitas, dan bantuan.

## Pengujian

Pengujian memakai database MySQL terpisah agar tidak menyentuh data pengembangan.

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS \`product_m_test\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan config:clear
php artisan test
```

`phpunit.xml` sudah mengarah ke `product_m_test`. Kredensial database dibaca dari `.env`.

Konfigurasi yang di-cache harus dibersihkan sebelum menjalankan test:

```bash
php artisan optimize:clear
```

## Scheduler

Pemeriksa ambang batas dijalankan setiap 5 menit:

```bash
php artisan monitoring:check-alerts
```

Untuk lokal:

```bash
php artisan schedule:work
```

Di server, tambahkan cron:

```cron
* * * * * cd /path/ke/aplikasi && php artisan schedule:run >> /dev/null 2>&1
```

## Stress Test

Buat data uji:

```bash
php artisan products:stress-seed 10000 --fresh
php artisan products:stress-seed 50000 --fresh
```

Bersihkan data uji:

```bash
php artisan products:stress-seed --clean
```

Jalankan k6 (memerlukan k6 terpasang):

```bash
K6_NO_COOKIES_RESET=true BASE_URL=http://127.0.0.1:8000 \
  k6 run tests/Stress/search.js
```

`K6_NO_COOKIES_RESET=true` wajib agar cookie sesi login tidak dihapus di antara iterasi k6. VU dan durasi dapat diatur:

```bash
K6_NO_COOKIES_RESET=true BASE_URL=http://127.0.0.1:8000 \
  SEARCH_VUS=5 CATALOG_VUS=3 DURATION=20s \
  k6 run tests/Stress/search.js
```

Untuk hasil yang lebih representatif, gunakan Nginx + PHP-FPM, `APP_DEBUG=false`, dan `php artisan optimize`. Hasil pengujian ada pada `docs/03-laporan-pengujian.docx`.

## Struktur Penting

```text
app/Http/Controllers     Controller tipis
app/Http/Middleware      Idle timeout, role, performance logger
app/Http/Requests        Validasi formulir dan filter
app/Services             ProductService, ActivityLogService, PerformanceLogService, AlertService
app/Models               Product, Category, ActivityLog, PerformanceLog, Alert, User
database/migrations      Skema database
database/seeders         Data awal dan akun demo
resources/views          Tampilan AdminLTE
tests/Feature            Pengujian integrasi dan sistem
tests/Stress             Skrip k6
docs                     Dokumentasi teknis, pengguna, dan laporan pengujian
```

## Catatan

- Kategori hanya di-seed; belum ada CRUD kategori.
- Produk dihapus secara lunak. Nama/SKU tetap unik termasuk terhadap produk yang sudah dihapus.
- Peringatan otomatis tampil di dashboard (belum ada notifikasi email).
- Sesi berakhir otomatis setelah `SESSION_IDLE_TIMEOUT` menit tanpa aktivitas.
- Dokumentasi `.docx`:
  - `docs/01-dokumentasi-teknis.docx`
  - `docs/02-dokumentasi-pengguna.docx`
  - `docs/03-laporan-pengujian.docx`
