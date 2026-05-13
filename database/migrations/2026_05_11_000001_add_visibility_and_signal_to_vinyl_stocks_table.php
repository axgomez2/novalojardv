<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vinyl_stocks', function (Blueprint $table) {
            // Visibilidade: public = aparece no site; private_preorder = só via pré-venda direta (oculto do catálogo)
            $table->string('visibility', 20)->default('public')->after('availability')->index();

            // Percentual padrão do sinal (ex: 50.00 = 50%). Se null, admin define no momento da pré-venda.
            $table->decimal('default_signal_percentage', 5, 2)->nullable()->after('visibility');
        });
    }

    public function down(): void
    {
        Schema::table('vinyl_stocks', function (Blueprint $table) {
            $table->dropIndex(['visibility']);
            $table->dropColumn(['visibility', 'default_signal_percentage']);
        });
    }
};
