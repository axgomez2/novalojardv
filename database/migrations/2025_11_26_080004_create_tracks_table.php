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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vinyl_master_id');
            $table->string('position')->nullable(); // A1, A2, B1, B2, etc
            $table->string('name');
            $table->string('duration')->nullable(); // 3:45 format
            $table->integer('duration_seconds')->nullable();
            $table->string('youtube_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('vinyl_master_id')->references('id')->on('vinyl_masters')->onDelete('cascade');
            
            $table->index(['vinyl_master_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
