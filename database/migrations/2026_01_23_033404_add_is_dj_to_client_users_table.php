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
        // Adicionar campo is_dj na tabela client_users
        Schema::table('client_users', function (Blueprint $table) {
            $table->boolean('is_dj')->default(false)->after('is_active');
        });

        // Adicionar relacionamento com client_user na tabela dj_playlists
        Schema::table('dj_playlists', function (Blueprint $table) {
            $table->uuid('client_user_id')->nullable()->after('id');
            $table->foreign('client_user_id')
                  ->references('id')
                  ->on('client_users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dj_playlists', function (Blueprint $table) {
            $table->dropForeign(['client_user_id']);
            $table->dropColumn('client_user_id');
        });

        Schema::table('client_users', function (Blueprint $table) {
            $table->dropColumn('is_dj');
        });
    }
};
