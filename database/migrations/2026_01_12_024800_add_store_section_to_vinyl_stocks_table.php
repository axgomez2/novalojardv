<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona campo para separar discos em seções da loja:
     * - dj: Discos para DJs (singles, maxis, promos, etc)
     * - albums: Álbuns nacionais/internacionais para colecionadores
     */
    public function up(): void
    {
        Schema::table('vinyl_stocks', function (Blueprint $table) {
            $table->enum('store_section', ['dj', 'albums'])->default('dj')->after('availability');
            $table->index('store_section');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vinyl_stocks', function (Blueprint $table) {
            $table->dropIndex(['store_section']);
            $table->dropColumn('store_section');
        });
    }
};
