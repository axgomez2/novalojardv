<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vinyl_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vinyl_master_id');
            
            // Identificação
            $table->string('catalog_number')->nullable()->comment('Número de catálogo');
            $table->string('barcode')->nullable()->comment('Código de barras oficial');
            $table->string('internal_code')->nullable()->unique()->comment('Código interno da loja');
            
            // Características físicas
            $table->string('format')->nullable()->comment('LP, 7", 10", 12"');
            $table->integer('num_discs')->default(1)->comment('Quantidade de discos');
            $table->string('speed')->nullable()->comment('33, 45, 78 RPM');
            $table->string('color')->nullable()->comment('Cor do vinil');
            $table->string('edition')->nullable()->comment('Edição especial, limitada, etc');
            
            // Condição
            $table->boolean('is_new')->default(true)->comment('Novo ou Usado');
            $table->unsignedBigInteger('media_status_id')->nullable();
            $table->unsignedBigInteger('cover_status_id')->nullable();
            
            // Dimensões e peso
            $table->unsignedBigInteger('weight_id')->nullable();
            $table->unsignedBigInteger('dimension_id')->nullable();
            
            // Fornecedor
            $table->unsignedBigInteger('supplier_id')->nullable();
            
            // Estoque
            $table->integer('stock')->default(0);
            $table->integer('stock_min')->default(0)->comment('Estoque mínimo para alerta');
            
            // Preços
            $table->decimal('cost_price', 10, 2)->nullable()->comment('Preço de custo/compra');
            $table->decimal('sell_price', 10, 2)->comment('Preço de venda');
            $table->decimal('promotional_price', 10, 2)->nullable()->comment('Preço promocional');
            $table->boolean('is_promotional')->default(false);
            $table->dateTime('promo_starts_at')->nullable();
            $table->dateTime('promo_ends_at')->nullable();
            
            // Status de disponibilidade
            $table->enum('availability', ['available', 'unavailable', 'featured', 'preorder'])->default('available');
            $table->date('release_date')->nullable()->comment('Data de lançamento para pré-venda');
            
            // Observações
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('vinyl_master_id')->references('id')->on('vinyl_masters')->onDelete('cascade');
            $table->foreign('media_status_id')->references('id')->on('media_statuses')->onDelete('set null');
            $table->foreign('cover_status_id')->references('id')->on('cover_statuses')->onDelete('set null');
            $table->foreign('weight_id')->references('id')->on('weights')->onDelete('set null');
            $table->foreign('dimension_id')->references('id')->on('dimensions')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');

            // Indexes
            $table->index('vinyl_master_id');
            $table->index('supplier_id');
            $table->index('availability');
            $table->index('is_new');
            $table->index('is_promotional');
            $table->index(['sell_price', 'is_promotional']);
        });

        // Add foreign key to category_vinyl_stock after vinyl_stocks exists
        Schema::table('category_vinyl_stock', function (Blueprint $table) {
            $table->foreign('vinyl_stock_id')->references('id')->on('vinyl_stocks')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('category_vinyl_stock', function (Blueprint $table) {
            $table->dropForeign(['vinyl_stock_id']);
        });
        Schema::dropIfExists('vinyl_stocks');
    }
};
