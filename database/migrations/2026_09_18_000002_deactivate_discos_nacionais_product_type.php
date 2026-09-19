<?php

use App\Models\ProductType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Desativar "Discos Nacionais" - não será mais utilizado
        ProductType::where('slug', 'discos-nacionais')->update(['is_active' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        ProductType::where('slug', 'discos-nacionais')->update(['is_active' => true]);
    }
};
