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
        // Adicionar campos extras na tabela de pedidos
        Schema::table('client_orders', function (Blueprint $table) {
            // Origem do pedido (online ou PDV)
            $table->enum('source', ['online', 'pdv'])->default('online')->after('order_number');
            
            // Usuário admin que criou (para PDV)
            $table->foreignId('created_by')->nullable()->after('source')->constrained('users')->nullOnDelete();
            
            // Cliente não cadastrado (para PDV)
            $table->string('guest_name')->nullable()->after('client_user_id');
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->string('guest_cpf', 14)->nullable();
            
            // Endereço inline para clientes não cadastrados
            $table->text('shipping_address_data')->nullable();
            
            // Envio - Melhor Envio
            $table->string('shipping_service_id')->nullable()->after('shipping_method');
            $table->string('shipping_service_name')->nullable();
            $table->string('shipping_carrier')->nullable();
            $table->integer('shipping_deadline')->nullable();
            $table->json('shipping_response')->nullable();
            
            // Invoice/NF-e
            $table->string('invoice_number')->nullable();
            $table->timestamp('invoice_generated_at')->nullable();
            $table->string('nfe_number')->nullable();
            $table->string('nfe_key', 44)->nullable();
            $table->string('nfe_protocol')->nullable();
            $table->timestamp('nfe_issued_at')->nullable();
            $table->enum('nfe_status', ['pending', 'issued', 'cancelled', 'error'])->nullable();
            
            // Tornar client_user_id nullable para pedidos de clientes não cadastrados
            $table->uuid('client_user_id')->nullable()->change();
        });

        // Histórico de status do pedido
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('client_orders')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('order_id');
        });

        // Invoices/Declarações de Conteúdo
        Schema::create('order_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('client_orders')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->enum('type', ['content_declaration', 'nfe'])->default('content_declaration');
            
            // Dados do remetente
            $table->string('sender_name');
            $table->string('sender_cpf_cnpj', 18);
            $table->text('sender_address');
            
            // Dados do destinatário
            $table->string('recipient_name');
            $table->string('recipient_cpf_cnpj', 18)->nullable();
            $table->text('recipient_address');
            
            // Valores
            $table->decimal('total_value', 10, 2);
            $table->decimal('shipping_value', 10, 2)->default(0);
            
            // Itens (JSON com lista de itens)
            $table->json('items');
            
            // PDF gerado
            $table->string('pdf_path')->nullable();
            
            // NF-e (quando emitida)
            $table->string('nfe_key', 44)->nullable();
            $table->string('nfe_protocol')->nullable();
            $table->text('nfe_xml')->nullable();
            $table->timestamp('nfe_issued_at')->nullable();
            
            $table->timestamps();

            $table->index('order_id');
            $table->index('invoice_number');
        });

        // Notificações enviadas
        Schema::create('order_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('client_orders')->cascadeOnDelete();
            $table->enum('channel', ['email', 'whatsapp', 'sms']);
            $table->enum('type', [
                'order_created',
                'payment_confirmed',
                'order_processing',
                'order_shipped',
                'order_delivered',
                'order_cancelled',
                'tracking_update'
            ]);
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index(['channel', 'status']);
        });

        // Configurações de envio (Melhor Envio)
        Schema::create('shipping_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_settings');
        Schema::dropIfExists('order_notifications');
        Schema::dropIfExists('order_invoices');
        Schema::dropIfExists('order_status_history');

        Schema::table('client_orders', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'source',
                'created_by',
                'guest_name',
                'guest_email',
                'guest_phone',
                'guest_cpf',
                'shipping_address_data',
                'shipping_service_id',
                'shipping_service_name',
                'shipping_carrier',
                'shipping_deadline',
                'shipping_response',
                'invoice_number',
                'invoice_generated_at',
                'nfe_number',
                'nfe_key',
                'nfe_protocol',
                'nfe_issued_at',
                'nfe_status',
            ]);
        });
    }
};
