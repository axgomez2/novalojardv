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
        Schema::create('client_addresses', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_user_id');
            $table->string('label', 50)->nullable(); // Casa, Trabalho, etc.
            $table->string('recipient_name')->nullable(); // Nome do destinatário
            $table->string('street', 255); // Rua/Avenida
            $table->string('number', 20); // Número
            $table->string('complement', 100)->nullable(); // Complemento/Apartamento
            $table->string('neighborhood', 100); // Bairro
            $table->string('city', 100); // Cidade
            $table->string('state', 2); // Estado (SP, RJ, MG, etc.)
            $table->string('zip_code', 9); // CEP (formato: 12345-678)
            $table->string('reference', 255)->nullable(); // Ponto de referência
            $table->boolean('is_default')->default(false); // Endereço padrão
            $table->timestamps();

            // Índices
            $table->index('client_user_id');
            $table->index('zip_code');
            $table->index(['client_user_id', 'is_default']);

            // Foreign Key
            $table->foreign('client_user_id')->references('id')->on('client_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_addresses');
    }
};
