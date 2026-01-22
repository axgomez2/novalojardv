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
        Schema::table('vinyl_masters', function (Blueprint $table) {
            // Remove unique from discogs_master_id (multiple releases can have same master)
            $table->dropUnique(['discogs_master_id']);
            
            // Add unique to discogs_release_id (each release is unique)
            $table->unique('discogs_release_id');
            
            // Add index to discogs_master_id for performance
            $table->index('discogs_master_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vinyl_masters', function (Blueprint $table) {
            $table->dropIndex(['discogs_master_id']);
            $table->dropUnique(['discogs_release_id']);
            $table->unique('discogs_master_id');
        });
    }
};
