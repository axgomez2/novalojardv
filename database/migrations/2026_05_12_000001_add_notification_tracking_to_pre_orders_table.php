<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pre_orders', function (Blueprint $table) {
            $table->timestamp('signal_reminder_sent_at')->nullable()->after('cancellation_reason');
            $table->timestamp('signal_overdue_notified_at')->nullable()->after('signal_reminder_sent_at');
            $table->timestamp('arrival_notified_at')->nullable()->after('signal_overdue_notified_at');
            $table->timestamp('balance_reminder_sent_at')->nullable()->after('arrival_notified_at');
            $table->timestamp('balance_overdue_notified_at')->nullable()->after('balance_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('pre_orders', function (Blueprint $table) {
            $table->dropColumn([
                'signal_reminder_sent_at',
                'signal_overdue_notified_at',
                'arrival_notified_at',
                'balance_reminder_sent_at',
                'balance_overdue_notified_at',
            ]);
        });
    }
};
