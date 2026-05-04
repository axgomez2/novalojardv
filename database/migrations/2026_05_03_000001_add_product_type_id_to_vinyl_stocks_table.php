<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona o vínculo entre vinyl_stocks e product_types.
     *
     * O campo é nullable por enquanto para permitir o backfill via seeder.
     * Após o seeder rodar (ProductTypeSeeder), todos os stocks existentes
     * terão um product_type_id atribuído conforme is_new (novo/usado).
     */
    public function up(): void
    {
        Schema::table('vinyl_stocks', function (Blueprint $table) {
            $table->foreignId('product_type_id')
                ->nullable()
                ->after('vinyl_master_id')
                ->constrained('product_types')
                ->nullOnDelete();

            $table->index('product_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('vinyl_stocks', function (Blueprint $table) {
            $table->dropForeign(['product_type_id']);
            $table->dropIndex(['product_type_id']);
            $table->dropColumn('product_type_id');
        });
    }
};
