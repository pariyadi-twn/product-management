<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SeedStressProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:stress-seed
                            {count=10000 : Jumlah produk uji yang dibuat}
                            {--fresh : Hapus data uji lama sebelum membuat data baru}
                            {--clean : Hapus data uji tanpa membuat data baru}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buat data produk dalam jumlah besar untuk pengujian stress pada katalog dan pencarian';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deleted = DB::table('products')->where('sku', 'like', 'STR-%')->delete();

        if ($deleted > 0) {
            $this->info($deleted.' produk uji lama dihapus.');
        }

        Cache::forget('products.summary');

        if ($this->option('clean')) {
            $this->info('Data uji dibersihkan.');

            return self::SUCCESS;
        }

        $count = (int) $this->argument('count');

        if ($count < 1) {
            $this->error('Jumlah produk harus lebih dari 0.');

            return self::FAILURE;
        }

        if (Category::query()->doesntExist()) {
            (new CategorySeeder)->run();
        }

        $categoryIds = Category::query()->pluck('id')->all();
        $adminId = User::query()->where('role', User::ROLE_ADMIN)->value('id');
        $chunkSize = 500;
        $now = now();

        $this->output->progressStart((int) ceil($count / $chunkSize));

        for ($start = 1; $start <= $count; $start += $chunkSize) {
            $end = min($start + $chunkSize - 1, $count);
            $rows = [];

            for ($index = $start; $index <= $end; $index++) {
                $rows[] = [
                    'sku' => sprintf('STR-%06d', $index),
                    'name' => 'Produk Stress '.str_pad((string) $index, 6, '0', STR_PAD_LEFT),
                    'category_id' => $categoryIds[($index - 1) % count($categoryIds)],
                    'price' => 1000 + (($index * 137) % 5000000),
                    'stock' => ($index * 7) % 150,
                    'status' => Product::STATUS_AKTIF,
                    'description' => 'Data uji beban nomor '.$index.'.',
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('products')->insertOrIgnore($rows);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        Cache::forget('products.summary');

        $total = Product::query()->where('sku', 'like', 'STR-%')->count();

        $this->info($total.' produk uji tersedia di database.');

        return self::SUCCESS;
    }
}
