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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->enum('severity', ['info', 'warning', 'critical']);
            $table->string('message', 255);
            $table->decimal('metric_value', 10, 2);
            $table->decimal('threshold', 10, 2);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('triggered_at');
            $table->timestamp('resolved_at')->nullable();

            $table->index(['type', 'is_resolved']);
            $table->index('triggered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
