<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class ProductSeeder extends Seeder
{
    /**
     * Seed the application's products.
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');
        $adminId = User::query()->where('role', User::ROLE_ADMIN)->value('id');

        $products = [
            ['sku' => 'ELK-001', 'name' => 'Laptop Pro 14', 'category' => 'elektronik', 'price' => 14500000, 'stock' => 12, 'status' => 'aktif', 'description' => 'Laptop 14 inci dengan prosesor generasi terbaru, RAM 16 GB, dan SSD 512 GB.'],
            ['sku' => 'ELK-002', 'name' => 'Mouse Wireless', 'category' => 'elektronik', 'price' => 185000, 'stock' => 40, 'status' => 'aktif', 'description' => 'Mouse nirkabel ergonomis dengan koneksi Bluetooth dan USB receiver.'],
            ['sku' => 'ELK-003', 'name' => 'Keyboard Mekanik', 'category' => 'elektronik', 'price' => 750000, 'stock' => 8, 'status' => 'aktif', 'description' => 'Keyboard mekanik 87 tombol dengan switch tactile dan lampu latar.'],
            ['sku' => 'ELK-004', 'name' => 'Monitor LED 24 Inci', 'category' => 'elektronik', 'price' => 1850000, 'stock' => 15, 'status' => 'aktif', 'description' => 'Monitor Full HD 24 inci dengan panel IPS dan koneksi HDMI.'],
            ['sku' => 'ELK-005', 'name' => 'Kabel HDMI 2 Meter', 'category' => 'elektronik', 'price' => 85000, 'stock' => 0, 'status' => 'aktif', 'description' => 'Kabel HDMI 2.0 sepanjang 2 meter untuk resolusi hingga 4K.'],
            ['sku' => 'ELK-006', 'name' => 'SSD NVMe 1TB', 'category' => 'elektronik', 'price' => 1250000, 'stock' => 6, 'status' => 'aktif', 'description' => 'Penyimpanan SSD NVMe berkapasitas 1 TB dengan kecepatan baca tinggi.'],
            ['sku' => 'ELK-007', 'name' => 'Headset Bluetooth', 'category' => 'elektronik', 'price' => 650000, 'stock' => 25, 'status' => 'aktif', 'description' => 'Headset over-ear dengan noise cancelling dan baterai tahan lama.'],
            ['sku' => 'ELK-008', 'name' => 'Webcam Full HD', 'category' => 'elektronik', 'price' => 450000, 'stock' => 3, 'status' => 'aktif', 'description' => 'Kamera web 1080p dengan mikrofon bawaan untuk rapat daring.'],

            ['sku' => 'PKN-001', 'name' => 'Kaos Polos Katun', 'category' => 'pakaian', 'price' => 85000, 'stock' => 60, 'status' => 'aktif', 'description' => 'Kaos polos bahan katun combed 30s yang nyaman dipakai harian.'],
            ['sku' => 'PKN-002', 'name' => 'Kemeja Formal', 'category' => 'pakaian', 'price' => 250000, 'stock' => 18, 'status' => 'aktif', 'description' => 'Kemeja formal lengan panjang dengan potongan slim fit.'],
            ['sku' => 'PKN-003', 'name' => 'Celana Chino', 'category' => 'pakaian', 'price' => 320000, 'stock' => 9, 'status' => 'aktif', 'description' => 'Celana chino berbahan stretch dengan warna netral.'],
            ['sku' => 'PKN-004', 'name' => 'Jaket Hoodie', 'category' => 'pakaian', 'price' => 375000, 'stock' => 0, 'status' => 'aktif', 'description' => 'Jaket hoodie dengan bahan fleece hangat dan kantong depan.'],
            ['sku' => 'PKN-005', 'name' => 'Sepatu Sneakers', 'category' => 'pakaian', 'price' => 550000, 'stock' => 14, 'status' => 'aktif', 'description' => 'Sepatu sneakers kasual dengan sol karet anti-slip.'],
            ['sku' => 'PKN-006', 'name' => 'Tas Ransel', 'category' => 'pakaian', 'price' => 420000, 'stock' => 7, 'status' => 'aktif', 'description' => 'Tas ransel dengan kompartemen laptop 14 inci dan bahan tahan air.'],

            ['sku' => 'MKN-001', 'name' => 'Kopi Arabika 250g', 'category' => 'makanan-minuman', 'price' => 95000, 'stock' => 30, 'status' => 'aktif', 'description' => 'Biji kopi arabika pilihan yang disangrai medium, kemasan 250 gram.'],
            ['sku' => 'MKN-002', 'name' => 'Teh Hijau Premium', 'category' => 'makanan-minuman', 'price' => 45000, 'stock' => 50, 'status' => 'aktif', 'description' => 'Teh hijau premium dalam kemasan 100 gram.'],
            ['sku' => 'MKN-003', 'name' => 'Madu Hutan 500ml', 'category' => 'makanan-minuman', 'price' => 120000, 'stock' => 10, 'status' => 'aktif', 'description' => 'Madu hutan murni tanpa tambahan gula, kemasan 500 ml.'],
            ['sku' => 'MKN-004', 'name' => 'Snack Kacang 250g', 'category' => 'makanan-minuman', 'price' => 25000, 'stock' => 0, 'status' => 'aktif', 'description' => 'Kacang tanah roasted dengan bumbu gurih, kemasan 250 gram.'],
            ['sku' => 'MKN-005', 'name' => 'Air Mineral 600ml', 'category' => 'makanan-minuman', 'price' => 5000, 'stock' => 100, 'status' => 'aktif', 'description' => 'Air mineral dalam kemasan botol 600 ml.'],

            ['sku' => 'ATK-001', 'name' => 'Notebook A5', 'category' => 'alat-tulis', 'price' => 35000, 'stock' => 75, 'status' => 'aktif', 'description' => 'Buku catatan A5 dengan 120 lembar kertas bergaris.'],
            ['sku' => 'ATK-002', 'name' => 'Pulpen Gel', 'category' => 'alat-tulis', 'price' => 12000, 'stock' => 120, 'status' => 'aktif', 'description' => 'Pulpen gel hitam 0.5 mm dengan tinta cepat kering.'],
            ['sku' => 'ATK-003', 'name' => 'Sticky Notes', 'category' => 'alat-tulis', 'price' => 18000, 'stock' => 5, 'status' => 'aktif', 'description' => 'Catatan tempel berwarna ukuran 76 x 76 mm.'],
            ['sku' => 'ATK-004', 'name' => 'Map Dokumen', 'category' => 'alat-tulis', 'price' => 15000, 'stock' => 45, 'status' => 'aktif', 'description' => 'Map plastik untuk menyimpan dokumen ukuran A4.'],

            ['sku' => 'PRB-001', 'name' => 'Kursi Ergonomis', 'category' => 'perabot', 'price' => 1750000, 'stock' => 8, 'status' => 'aktif', 'description' => 'Kursi kerja ergonomis dengan penyangga punggung dan sandaran tangan.'],
            ['sku' => 'PRB-002', 'name' => 'Meja Kerja', 'category' => 'perabot', 'price' => 2250000, 'stock' => 5, 'status' => 'aktif', 'description' => 'Meja kerja minimalis ukuran 120 x 60 cm dengan rangka besi.'],
            ['sku' => 'PRB-003', 'name' => 'Lampu Meja LED', 'category' => 'perabot', 'price' => 275000, 'stock' => 20, 'status' => 'aktif', 'description' => 'Lampu meja LED dengan tingkat kecerahan yang dapat diatur.'],
            ['sku' => 'PRB-004', 'name' => 'Rak Buku', 'category' => 'perabot', 'price' => 950000, 'stock' => 0, 'status' => 'aktif', 'description' => 'Rak buku tiga tingkat berbahan kayu olahan.'],
            ['sku' => 'PRB-005', 'name' => 'Lemari Arsip', 'category' => 'perabot', 'price' => 1850000, 'stock' => 4, 'status' => 'aktif', 'description' => 'Lemari arsip dua pintu dengan kunci keamanan.'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'category_id' => $categories[$product['category']],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'status' => $product['status'],
                    'description' => $product['description'],
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ],
            );
        }

        Cache::forget('products.summary');
    }
}
