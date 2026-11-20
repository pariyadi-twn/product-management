<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['deleted_at', 'stock'], 'products_deleted_stock_index');
            $table->index(['deleted_at', 'category_id'], 'products_deleted_category_index');
            $table->index(['deleted_at', 'status'], 'products_deleted_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_deleted_stock_index');
            $table->dropIndex('products_deleted_category_index');
            $table->dropIndex('products_deleted_status_index');
        });
    }
};
