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
            $table->index(['deleted_at', 'created_at'], 'products_deleted_created_index');
            $table->index(['deleted_at', 'name'], 'products_deleted_name_index');
            $table->index(['deleted_at', 'price'], 'products_deleted_price_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_deleted_created_index');
            $table->dropIndex('products_deleted_name_index');
            $table->dropIndex('products_deleted_price_index');
        });
    }
};
