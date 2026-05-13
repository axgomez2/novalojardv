<?php

namespace App\Console\Commands;

use App\Enums\PreOrderStatus;
use App\Models\PreOrder;
use App\Notifications\PreOrderNotification;
use Illuminate\Console\Command;

class SendPreOrderReminders extends Command
{
    protected $signature = 'pre-orders:send-reminders
                            {--days-before=3 : Dias antes do vencimento para enviar lembrete}
                            {--dry-run : Simula sem enviar emails}';

    protected $description = 'Envia notificações automáticas de pré-vendas (lembretes, vencidos, chegou).';

    public function handle(): int
    {
        $daysBefore = (int) $this->option('days-before');
        $dryRun = (bool) $this->option('dry-run');
        $sent = 0;

        $this->info('Verificando pré-vendas... ' . ($dryRun ? '[DRY-RUN]' : ''));

        // 1. Sinal vencendo em N dias
        $signalDueSoon = PreOrder::with('client')
            ->awaitingSignal()
            ->whereNotNull('signal_due_date')
            ->whereDate('signal_due_date', '>=', now()->toDateString())
            ->whereDate('signal_due_date', '<=', now()->addDays($daysBefore)->toDateString())
            ->whereNull('signal_reminder_sent_at')
            ->get();

        foreach ($signalDueSoon as $po) {
            $this->notify($po, PreOrderNotification::TYPE_SIGNAL_DUE_SOON, 'signal_reminder_sent_at', $dryRun);
            $sent++;
        }

        // 2. Sinal vencido
        $signalOverdue = PreOrder::with('client')
            ->signalOverdue()
            ->whereNull('signal_overdue_notified_at')
            ->get();

        foreach ($signalOverdue as $po) {
            $this->notify($po, PreOrderNotification::TYPE_SIGNAL_OVERDUE, 'signal_overdue_notified_at', $dryRun);
            $sent++;
        }

        // 3. Chegou na loja
        $arrived = PreOrder::with('client')
            ->where('status', PreOrderStatus::Arrived->value)
            ->whereNull('arrival_notified_at')
            ->get();

        foreach ($arrived as $po) {
            $this->notify($po, PreOrderNotification::TYPE_ARRIVED, 'arrival_notified_at', $dryRun);
            $sent++;
        }

        // 4. Saldo vencendo em N dias
        $balanceDueSoon = PreOrder::with('client')
            ->awaitingBalance()
            ->whereNotNull('balance_due_date')
            ->whereDate('balance_due_date', '>=', now()->toDateString())
            ->whereDate('balance_due_date', '<=', now()->addDays($daysBefore)->toDateString())
            ->whereNull('balance_reminder_sent_at')
            ->get();

        foreach ($balanceDueSoon as $po) {
            $this->notify($po, PreOrderNotification::TYPE_BALANCE_DUE_SOON, 'balance_reminder_sent_at', $dryRun);
            $sent++;
        }

        // 5. Saldo vencido
        $balanceOverdue = PreOrder::with('client')
            ->balanceOverdue()
            ->whereNull('balance_overdue_notified_at')
            ->get();

        foreach ($balanceOverdue as $po) {
            $this->notify($po, PreOrderNotification::TYPE_BALANCE_OVERDUE, 'balance_overdue_notified_at', $dryRun);
            $sent++;
        }

        $this->info("Concluído. Notificações disparadas: {$sent}");
        return self::SUCCESS;
    }

    protected function notify(PreOrder $po, string $type, string $trackingField, bool $dryRun): void
    {
        if (!$po->client) {
            $this->warn("Pré-venda {$po->code} sem cliente — ignorada.");
            return;
        }

        $this->line(" → {$po->code} [{$type}] para {$po->client->email}");

        if ($dryRun) {
            return;
        }

        try {
            $po->client->notify(new PreOrderNotification($po, $type));
            $po->{$trackingField} = now();
            $po->saveQuietly();
        } catch (\Throwable $e) {
            $this->error("   Falha ao enviar: " . $e->getMessage());
            \Log::error('PreOrderNotification falhou', [
                'pre_order' => $po->code,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
