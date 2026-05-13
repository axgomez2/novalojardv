<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // ex: PV-2026-00001
            $table->uuid('client_user_id')->index();
            $table->foreign('client_user_id')->references('id')->on('client_users')->cascadeOnDelete();
            $table->foreignId('vinyl_stock_id')->constrained()->restrictOnDelete();

            $table->unsignedInteger('quantity')->default(1);

            // Valores monetários (salvos em snapshot para não dependerem do preço mudar depois)
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('signal_amount', 10, 2);
            $table->decimal('signal_percentage', 5, 2)->nullable();
            // balance_amount é computed: total_amount - signal_amount (calculado no app)

            // Status geral do pedido
            $table->string('status', 30)->default('awaiting_signal')->index();

            // Datas
            $table->date('expected_arrival_date')->nullable()->index();
            $table->date('signal_due_date')->nullable()->index();
            $table->date('balance_due_date')->nullable()->index();
            $table->timestamp('signal_paid_at')->nullable();
            $table->timestamp('balance_paid_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Método de pagamento efetivamente usado em cada etapa
            // gateway = pago via Mercado Pago; manual = admin confirmou PIX/transferência
            $table->string('signal_payment_method', 20)->nullable(); // gateway|manual
            $table->string('balance_payment_method', 20)->nullable();

            // Referências ao pagamento no MP (se gateway)
            $table->string('signal_payment_id')->nullable();
            $table->string('balance_payment_id')->nullable();

            // Endereço de entrega (snapshot no momento do pedido, JSON)
            $table->json('shipping_address')->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();

            // Observações
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_user_id', 'status']);
            $table->index(['status', 'signal_due_date']);
            $table->index(['status', 'balance_due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_orders');
    }
};
