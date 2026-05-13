<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pre_order_id')->constrained()->cascadeOnDelete();

            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);

            // Quem mudou: admin (app_users) ou sistema (jobs). User fica implícito pelo admin_id/null
            $table->unsignedBigInteger('admin_user_id')->nullable();
            $table->string('triggered_by', 30)->default('admin'); // admin|system|customer

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['pre_order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_order_status_histories');
    }
};
