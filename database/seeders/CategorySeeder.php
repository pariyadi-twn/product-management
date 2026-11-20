<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the application's categories.
     */
    public function run(): void
    {
        $categories = [
            ['slug' => 'elektronik', 'name' => 'Elektronik', 'description' => 'Perangkat keras, aksesori, dan gadget elektronik.'],
            ['slug' => 'pakaian', 'name' => 'Pakaian', 'description' => 'Pakaian pria, wanita, dan aksesori fashion.'],
            ['slug' => 'makanan-minuman', 'name' => 'Makanan & Minuman', 'description' => 'Produk makanan, minuman, dan bahan konsumsi.'],
            ['slug' => 'alat-tulis', 'name' => 'Alat Tulis', 'description' => 'Perlengkapan kantor, sekolah, dan kebutuhan tulis.'],
            ['slug' => 'perabot', 'name' => 'Perabot', 'description' => 'Furnitur dan perlengkapan ruang kerja.'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
