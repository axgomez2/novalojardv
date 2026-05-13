<?php

namespace App\Notifications;

use App\Models\PreOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PreOrderNotification extends Notification
{
    use Queueable;

    public const TYPE_SIGNAL_DUE_SOON = 'signal_due_soon';
    public const TYPE_SIGNAL_OVERDUE = 'signal_overdue';
    public const TYPE_ARRIVED = 'arrived';
    public const TYPE_BALANCE_DUE_SOON = 'balance_due_soon';
    public const TYPE_BALANCE_OVERDUE = 'balance_overdue';

    public function __construct(public PreOrder $preOrder, public string $type)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $po = $this->preOrder;
        $title = $po->vinylStock?->vinylMaster?->title ?? 'seu disco';
        $code = $po->code;
        $frontendUrl = config('app.frontend_url') ?: env('FRONTEND_URL', 'https://rdvdiscos.com.br');
        $detailUrl = rtrim($frontendUrl, '/') . '/minhas-pre-vendas/' . $code;
        $money = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');

        $msg = (new MailMessage())->greeting('Olá ' . ($notifiable->name ?? 'cliente') . '!');

        switch ($this->type) {
            case self::TYPE_SIGNAL_DUE_SOON:
                return $msg
                    ->subject("Lembrete: sinal da pré-venda {$code} vence em breve")
                    ->line("Sua pré-venda **{$code}** ({$title}) tem o sinal de **{$money($po->signal_amount)}** com vencimento em **{$po->signal_due_date?->format('d/m/Y')}**.")
                    ->action('Pagar sinal agora', $detailUrl)
                    ->line('Após a confirmação do sinal, sua pré-venda entra em produção/importação.');

            case self::TYPE_SIGNAL_OVERDUE:
                return $msg
                    ->subject("ATENÇÃO: sinal da pré-venda {$code} venceu")
                    ->line("O sinal da pré-venda **{$code}** ({$title}) venceu em **{$po->signal_due_date?->format('d/m/Y')}**.")
                    ->line("Valor: **{$money($po->signal_amount)}**")
                    ->action('Regularizar pagamento', $detailUrl)
                    ->line('Caso não receba o pagamento nos próximos dias, a pré-venda poderá ser cancelada.');

            case self::TYPE_ARRIVED:
                return $msg
                    ->subject("Boa notícia! Seu disco da pré-venda {$code} chegou")
                    ->line("O disco **{$title}** da pré-venda **{$code}** chegou na nossa loja!")
                    ->line("Para liberar o envio, falta apenas o pagamento do saldo: **{$money($po->balance_amount)}**.")
                    ->action('Pagar saldo', $detailUrl)
                    ->line('Assim que o pagamento for confirmado, preparamos o envio.');

            case self::TYPE_BALANCE_DUE_SOON:
                return $msg
                    ->subject("Lembrete: saldo da pré-venda {$code} vence em breve")
                    ->line("O saldo de **{$money($po->balance_amount)}** da pré-venda **{$code}** ({$title}) vence em **{$po->balance_due_date?->format('d/m/Y')}**.")
                    ->action('Pagar saldo agora', $detailUrl);

            case self::TYPE_BALANCE_OVERDUE:
                return $msg
                    ->subject("ATENÇÃO: saldo da pré-venda {$code} venceu")
                    ->line("O saldo da pré-venda **{$code}** ({$title}) venceu em **{$po->balance_due_date?->format('d/m/Y')}**.")
                    ->line("Valor: **{$money($po->balance_amount)}**")
                    ->action('Regularizar saldo', $detailUrl);
        }

        return $msg->subject('Atualização da sua pré-venda ' . $code)->action('Ver pré-venda', $detailUrl);
    }
}
