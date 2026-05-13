<?php

namespace App\Enums;

enum PreOrderStatus: string
{
    case AwaitingSignal = 'awaiting_signal';
    case SignalPaid = 'signal_paid';
    case InTransit = 'in_transit';
    case Arrived = 'arrived';
    case AwaitingBalance = 'awaiting_balance';
    case BalancePaid = 'balance_paid';
    case ReadyToShip = 'ready_to_ship';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingSignal => 'Aguardando sinal',
            self::SignalPaid => 'Sinal pago',
            self::InTransit => 'Em trânsito (fornecedor)',
            self::Arrived => 'Chegou na loja',
            self::AwaitingBalance => 'Aguardando saldo',
            self::BalancePaid => 'Saldo pago',
            self::ReadyToShip => 'Pronto para envio',
            self::Shipped => 'Enviado ao cliente',
            self::Delivered => 'Entregue',
            self::Cancelled => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AwaitingSignal => 'yellow',
            self::SignalPaid => 'blue',
            self::InTransit => 'indigo',
            self::Arrived => 'purple',
            self::AwaitingBalance => 'orange',
            self::BalancePaid => 'teal',
            self::ReadyToShip => 'cyan',
            self::Shipped => 'sky',
            self::Delivered => 'green',
            self::Cancelled => 'red',
        };
    }

    /**
     * Próximas transições possíveis a partir deste status.
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::AwaitingSignal => [self::SignalPaid, self::Cancelled],
            self::SignalPaid => [self::InTransit, self::Arrived, self::Cancelled],
            self::InTransit => [self::Arrived, self::Cancelled],
            self::Arrived => [self::AwaitingBalance, self::Cancelled],
            self::AwaitingBalance => [self::BalancePaid, self::Cancelled],
            self::BalancePaid => [self::ReadyToShip, self::Cancelled],
            self::ReadyToShip => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Delivered],
            self::Delivered => [],
            self::Cancelled => [],
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled]);
    }

    public function requiresSignalPayment(): bool
    {
        return $this === self::AwaitingSignal;
    }

    public function requiresBalancePayment(): bool
    {
        return $this === self::AwaitingBalance;
    }

    /**
     * Status "ativos" — mostrar em painéis e alertas.
     */
    public static function activeStatuses(): array
    {
        return array_map(fn ($c) => $c->value, array_filter(
            self::cases(),
            fn ($c) => !$c->isFinal()
        ));
    }

    public static function options(): array
    {
        return array_map(
            fn ($c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        );
    }
}
