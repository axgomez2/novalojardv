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
        Schema::table('client_users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('cpf', 14)->nullable()->after('phone'); // Formato: 123.456.789-00
            $table->date('birth_date')->nullable()->after('cpf');
            
            // Índices
            $table->index('cpf');
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_users', function (Blueprint $table) {
            $table->dropIndex(['cpf']);
            $table->dropIndex(['phone']);
            $table->dropColumn(['phone', 'cpf', 'birth_date']);
        });
    }
};
