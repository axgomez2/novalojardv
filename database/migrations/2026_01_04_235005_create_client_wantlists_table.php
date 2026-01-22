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
        // Wantlist - Lista de procura (itens que o cliente procura mas não estão disponíveis)
        Schema::create('client_wantlists', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_user_id');
            $table->unsignedBigInteger('vinyl_master_id')->nullable(); // Pode ser um disco específico
            $table->string('artist_name')->nullable(); // Ou apenas o nome do artista
            $table->string('album_name')->nullable(); // E nome do álbum
            $table->string('release_year', 4)->nullable();
            $table->text('description')->nullable(); // Descrição adicional
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->decimal('max_price', 10, 2)->nullable(); // Preço máximo que pagaria
            $table->boolean('notify_when_available')->default(true);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->foreign('client_user_id')->references('id')->on('client_users')->onDelete('cascade');
            $table->foreign('vinyl_master_id')->references('id')->on('vinyl_masters')->onDelete('set null');
            
            $table->index('client_user_id');
            $table->index('vinyl_master_id');
            $table->index(['notify_when_available', 'notified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_wantlists');
    }
};
