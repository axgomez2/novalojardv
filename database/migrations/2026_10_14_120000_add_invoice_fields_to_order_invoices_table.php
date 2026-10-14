<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_invoices', function (Blueprint $table) {
            $table->string('sender_legal_name')->nullable()->after('sender_name');
            $table->string('sender_ie')->nullable()->after('sender_cpf_cnpj');
            $table->string('sender_phone')->nullable()->after('sender_address');
            $table->string('sender_email')->nullable()->after('sender_phone');
            $table->string('recipient_phone')->nullable()->after('recipient_address');
        });
    }

    public function down(): void
    {
        Schema::table('order_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'sender_legal_name',
                'sender_ie',
                'sender_phone',
                'sender_email',
                'recipient_phone',
            ]);
        });
    }
};
