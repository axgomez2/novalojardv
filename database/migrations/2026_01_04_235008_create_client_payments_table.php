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
        // Pagamentos
        Schema::create('client_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->uuid('client_user_id');
            
            // Método de pagamento
            $table->enum('payment_method', [
                'pix',
                'credit_card',
                'debit_card',
                'boleto',
                'bank_transfer'
            ]);
            
            // Status do pagamento
            $table->enum('status', [
                'pending',      // Aguardando
                'processing',   // Processando
                'approved',     // Aprovado
                'declined',     // Recusado
                'cancelled',    // Cancelado
                'refunded',     // Reembolsado
                'chargeback'    // Estorno
            ])->default('pending');
            
            // Valores
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2)->default(0); // Taxa do gateway
            $table->decimal('net_amount', 10, 2)->nullable(); // Valor líquido
            
            // Gateway de pagamento
            $table->string('gateway', 50)->nullable(); // mercadopago, pagseguro, stripe, etc.
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_payment_id')->nullable();
            $table->json('gateway_response')->nullable();
            
            // Cartão (se aplicável)
            $table->string('card_brand', 20)->nullable();
            $table->string('card_last_digits', 4)->nullable();
            $table->integer('installments')->default(1);
            
            // PIX (se aplicável)
            $table->text('pix_qr_code')->nullable();
            $table->string('pix_qr_code_base64')->nullable();
            $table->timestamp('pix_expiration')->nullable();
            
            // Boleto (se aplicável)
            $table->string('boleto_url')->nullable();
            $table->string('boleto_barcode')->nullable();
            $table->date('boleto_due_date')->nullable();
            
            // Datas
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('client_orders')->onDelete('cascade');
            $table->foreign('client_user_id')->references('id')->on('client_users')->onDelete('cascade');
            
            $table->index('order_id');
            $table->index('client_user_id');
            $table->index('status');
            $table->index('gateway_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_payments');
    }
};
