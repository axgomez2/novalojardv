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
        // Pedidos
        Schema::create('client_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->uuid('client_user_id');
            $table->unsignedBigInteger('shipping_address_id')->nullable();
            $table->unsignedBigInteger('billing_address_id')->nullable();
            
            // Status do pedido
            $table->enum('status', [
                'pending',      // Aguardando pagamento
                'paid',         // Pago
                'processing',   // Em processamento
                'shipped',      // Enviado
                'delivered',    // Entregue
                'cancelled',    // Cancelado
                'refunded'      // Reembolsado
            ])->default('pending');
            
            // Valores
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            
            // Cupom de desconto
            $table->string('coupon_code', 50)->nullable();
            $table->decimal('coupon_discount', 10, 2)->nullable();
            
            // Envio
            $table->string('shipping_method', 100)->nullable();
            $table->string('tracking_code', 100)->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Observações
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('client_user_id')->references('id')->on('client_users')->onDelete('cascade');
            $table->foreign('shipping_address_id')->references('id')->on('client_addresses')->onDelete('set null');
            $table->foreign('billing_address_id')->references('id')->on('client_addresses')->onDelete('set null');
            
            $table->index('client_user_id');
            $table->index('status');
            $table->index('created_at');
            $table->index(['client_user_id', 'status']);
        });

        // Itens do pedido
        Schema::create('client_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('vinyl_stock_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('client_orders')->onDelete('cascade');
            $table->foreign('vinyl_stock_id')->references('id')->on('vinyl_stocks')->onDelete('restrict');
            
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_order_items');
        Schema::dropIfExists('client_orders');
    }
};
