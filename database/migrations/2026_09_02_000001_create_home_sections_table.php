<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->enum('type', ['discos_novos', 'pre_venda', 'discos_usados', 'discos_nacionais', 'ofertas', 'destaques'])->default('discos_novos');
            $table->integer('max_items')->default(20);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('view_all_link')->nullable();
            $table->timestamps();
        });

        Schema::create('home_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_section_id')->constrained()->onDelete('cascade');
            $table->foreignId('vinyl_stock_id')->constrained()->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->unique(['home_section_id', 'vinyl_stock_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_section_items');
        Schema::dropIfExists('home_sections');
    }
};
