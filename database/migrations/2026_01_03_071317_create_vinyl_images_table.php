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
        Schema::create('vinyl_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vinyl_master_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('local'); // local, discogs
            $table->string('url'); // URL da imagem (local ou externa)
            $table->string('path')->nullable(); // Caminho do arquivo local
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('size')->nullable(); // Tamanho em bytes
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['vinyl_master_id', 'is_primary']);
            $table->index(['vinyl_master_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vinyl_images');
    }
};
