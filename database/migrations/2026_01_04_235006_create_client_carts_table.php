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
        // Carrinho de compras
        Schema::create('client_carts', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_user_id')->nullable(); // Nullable para carrinho de visitante
            $table->string('session_id')->nullable(); // Para carrinho de visitante
            $table->timestamps();

            $table->foreign('client_user_id')->references('id')->on('client_users')->onDelete('cascade');
            
            $table->index('client_user_id');
            $table->index('session_id');
        });

        // Itens do carrinho
        Schema::create('client_cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('vinyl_stock_id');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2); // Preço no momento da adição
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on('client_carts')->onDelete('cascade');
            $table->foreign('vinyl_stock_id')->references('id')->on('vinyl_stocks')->onDelete('cascade');
            
            $table->unique(['cart_id', 'vinyl_stock_id']);
            $table->index('cart_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_cart_items');
        Schema::dropIfExists('client_carts');
    }
};
