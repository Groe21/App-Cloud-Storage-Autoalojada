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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('folder_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->string('name');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type');
            $table->string('extension', 10);
            $table->bigInteger('size_bytes');
            $table->string('hash', 64)->nullable(); // SHA256 hash
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('folder_id');
            $table->index('mime_type');
            $table->index('hash');
            $table->index(['user_id', 'folder_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
