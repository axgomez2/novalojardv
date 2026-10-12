<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de configuração do sorteio
        Schema::create('raffle_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Promoção RDV Discos 2026');
            $table->text('regulation')->nullable(); // Regulamento completo
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('draw_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('starting_number')->default(1000);
            $table->integer('current_number')->default(1000);
            $table->timestamps();
        });

        // Tabela de números do sorteio
        Schema::create('raffle_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_user_id')->constrained('client_users')->onDelete('cascade');
            $table->integer('number')->unique(); // Número único do sorteio
            $table->string('purchase_description')->nullable(); // Descrição opcional da compra
            $table->string('order_number')->nullable(); // Número do pedido relacionado
            $table->enum('status', ['active', 'cancelled', 'winner'])->default('active');
            $table->string('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['client_user_id', 'status']);
            $table->index('number');
        });

        // Tabela de aceite dos termos
        Schema::create('raffle_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_user_id')->constrained('client_users')->onDelete('cascade');
            $table->boolean('accepted')->default(false);
            $table->timestamp('accepted_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->unique('client_user_id');
        });

        // Tabela de prêmios
        Schema::create('raffle_prizes', function (Blueprint $table) {
            $table->id();
            $table->integer('position'); // 1 = Grande Prêmio, 2 = 2º sorteio, etc.
            $table->string('name');
            $table->text('description');
            $table->decimal('value', 10, 2)->nullable();
            $table->foreignUuid('winner_client_id')->nullable()->constrained('client_users')->nullOnDelete();
            $table->integer('winning_number')->nullable();
            $table->timestamp('drawn_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffle_prizes');
        Schema::dropIfExists('raffle_acceptances');
        Schema::dropIfExists('raffle_numbers');
        Schema::dropIfExists('raffle_settings');
    }
};
