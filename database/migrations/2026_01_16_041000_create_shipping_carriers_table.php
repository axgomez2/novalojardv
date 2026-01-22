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
        // Transportadoras disponíveis (Melhor Envio)
        Schema::create('shipping_carriers', function (Blueprint $table) {
            $table->id();
            $table->string('melhor_envio_id')->unique();
            $table->string('name');
            $table->string('company');
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('additional_cost', 10, 2)->default(0);
            $table->decimal('additional_percentage', 5, 2)->default(0);
            $table->integer('additional_days')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Configurações de pré-venda e datas de entrega
        Schema::create('preorder_shipping_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vinyl_stock_id')->nullable();
            $table->date('expected_availability_date')->nullable();
            $table->integer('additional_processing_days')->default(0);
            $table->text('shipping_notes')->nullable();
            $table->timestamps();

            $table->foreign('vinyl_stock_id')->references('id')->on('vinyl_stocks')->onDelete('cascade');
        });

        // Pagamentos (Mercado Pago)
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('client_orders')->cascadeOnDelete();
            $table->string('payment_method');
            $table->string('mercado_pago_id')->nullable();
            $table->string('mercado_pago_status')->nullable();
            $table->string('mercado_pago_status_detail')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->integer('installments')->default(1);
            $table->json('payer_info')->nullable();
            $table->json('payment_response')->nullable();
            $table->string('pix_qr_code')->nullable();
            $table->text('pix_qr_code_base64')->nullable();
            $table->timestamp('pix_expiration')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('mercado_pago_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_payments');
        Schema::dropIfExists('preorder_shipping_configs');
        Schema::dropIfExists('shipping_carriers');
    }
};
