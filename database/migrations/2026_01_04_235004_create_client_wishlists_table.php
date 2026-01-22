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
        // Wishlist - Lista de desejos (itens que o cliente quer comprar)
        Schema::create('client_wishlists', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_user_id');
            $table->unsignedBigInteger('vinyl_stock_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('client_user_id')->references('id')->on('client_users')->onDelete('cascade');
            $table->foreign('vinyl_stock_id')->references('id')->on('vinyl_stocks')->onDelete('cascade');
            
            $table->unique(['client_user_id', 'vinyl_stock_id']);
            $table->index('client_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_wishlists');
    }
};
