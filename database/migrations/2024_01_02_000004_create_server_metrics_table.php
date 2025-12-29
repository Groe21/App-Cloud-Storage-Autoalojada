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
        Schema::create('server_metrics', function (Blueprint $table) {
            $table->id();
            $table->decimal('cpu_usage', 5, 2)->nullable();
            $table->bigInteger('memory_total')->nullable();
            $table->bigInteger('memory_used')->nullable();
            $table->bigInteger('memory_free')->nullable();
            $table->decimal('memory_usage_percent', 5, 2)->nullable();
            $table->bigInteger('disk_total')->nullable();
            $table->bigInteger('disk_used')->nullable();
            $table->bigInteger('disk_free')->nullable();
            $table->decimal('disk_usage_percent', 5, 2)->nullable();
            $table->decimal('load_average_1', 8, 2)->nullable();
            $table->decimal('load_average_5', 8, 2)->nullable();
            $table->decimal('load_average_15', 8, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_metrics');
    }
};
