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
        Schema::create('performance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint', 150);
            $table->string('method', 10);
            $table->integer('duration_ms');
            $table->smallInteger('status_code');
            $table->integer('result_count')->nullable();
            $table->timestamp('recorded_at')->useCurrent();

            $table->index('recorded_at');
            $table->index(['endpoint', 'recorded_at']);
            $table->index(['status_code', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_logs');
    }
};
