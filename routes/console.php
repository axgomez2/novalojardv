<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Notificações diárias de pré-vendas (lembretes, vencidos, chegou)
Schedule::command('pre-orders:send-reminders')
    ->dailyAt('09:00')
    ->timezone('America/Sao_Paulo')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Log::error('Falha ao executar pre-orders:send-reminders');
    });
