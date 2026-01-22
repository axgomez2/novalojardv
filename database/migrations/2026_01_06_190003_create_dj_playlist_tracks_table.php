<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Faixas das Playlists de DJs
     */
    public function up(): void
    {
        Schema::create('dj_playlist_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dj_playlist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->integer('position')->default(1);
            $table->timestamps();

            $table->unique(['dj_playlist_id', 'track_id']);
            $table->unique(['dj_playlist_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dj_playlist_tracks');
    }
};
