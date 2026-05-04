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
        Schema::create('chart_vinyls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vinyl_master_id')->constrained()->cascadeOnDelete();
            $table->integer('position')->default(1);
            $table->timestamps();

            $table->unique(['chart_id', 'vinyl_master_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_vinyls');
    }
};
