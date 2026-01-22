<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vinyl_stock_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Usuário que realizou a movimentação');
            
            // Tipo de movimentação
            $table->enum('type', [
                'purchase',      // Compra/entrada
                'sale',          // Venda/saída
                'adjustment',    // Ajuste de inventário
                'return',        // Devolução
                'loss',          // Perda/extravio
                'transfer'       // Transferência
            ]);
            
            // Quantidade (positivo = entrada, negativo = saída)
            $table->integer('quantity');
            $table->integer('stock_before')->comment('Estoque antes da movimentação');
            $table->integer('stock_after')->comment('Estoque após a movimentação');
            
            // Valores
            $table->decimal('unit_price', 10, 2)->nullable()->comment('Preço unitário da operação');
            $table->decimal('total_price', 10, 2)->nullable()->comment('Valor total da operação');
            
            // Referência
            $table->string('reference')->nullable()->comment('NF, pedido, etc');
            $table->text('notes')->nullable();
            
            $table->timestamps();

            $table->foreign('vinyl_stock_id')->references('id')->on('vinyl_stocks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index('vinyl_stock_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
