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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['payable', 'receivable']); // Conta a pagar ou a receber
            $table->string('description');
            $table->text('notes')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('due_date'); // Data de vencimento
            $table->date('payment_date')->nullable(); // Data de pagamento/recebimento
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->foreignId('payment_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('income_source_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recurring_payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->nullable(); // Número de nota fiscal, boleto, etc
            $table->string('payment_method')->nullable(); // PIX, boleto, cartão, etc
            $table->string('attachment')->nullable(); // Caminho do comprovante
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
