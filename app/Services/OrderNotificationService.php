<?php

namespace App\Services;

use App\Models\ClientOrder;
use App\Models\OrderNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    /**
     * Enviar notificação de pedido criado
     */
    public function notifyOrderCreated(ClientOrder $order): void
    {
        $this->sendEmailNotification($order, 'order_created', 'Pedido Recebido');
        $this->sendWhatsAppNotification($order, 'order_created');
    }

    /**
     * Enviar notificação de pagamento confirmado
     */
    public function notifyPaymentConfirmed(ClientOrder $order): void
    {
        $this->sendEmailNotification($order, 'payment_confirmed', 'Pagamento Confirmado');
        $this->sendWhatsAppNotification($order, 'payment_confirmed');
    }

    /**
     * Enviar notificação de pedido em processamento
     */
    public function notifyOrderProcessing(ClientOrder $order): void
    {
        $this->sendEmailNotification($order, 'order_processing', 'Pedido em Preparação');
        $this->sendWhatsAppNotification($order, 'order_processing');
    }

    /**
     * Enviar notificação de pedido enviado
     */
    public function notifyOrderShipped(ClientOrder $order): void
    {
        $this->sendEmailNotification($order, 'order_shipped', 'Pedido Enviado');
        $this->sendWhatsAppNotification($order, 'order_shipped');
    }

    /**
     * Enviar notificação de pedido entregue
     */
    public function notifyOrderDelivered(ClientOrder $order): void
    {
        $this->sendEmailNotification($order, 'order_delivered', 'Pedido Entregue');
        $this->sendWhatsAppNotification($order, 'order_delivered');
    }

    /**
     * Enviar notificação de pedido cancelado
     */
    public function notifyOrderCancelled(ClientOrder $order): void
    {
        $this->sendEmailNotification($order, 'order_cancelled', 'Pedido Cancelado');
        $this->sendWhatsAppNotification($order, 'order_cancelled');
    }

    /**
     * Enviar notificação de atualização de rastreio
     */
    public function notifyTrackingUpdate(ClientOrder $order): void
    {
        $this->sendEmailNotification($order, 'tracking_update', 'Atualização de Rastreio');
        $this->sendWhatsAppNotification($order, 'tracking_update');
    }

    /**
     * Enviar notificação por e-mail
     */
    protected function sendEmailNotification(ClientOrder $order, string $type, string $subject): void
    {
        $email = $order->customer_email;
        
        if (!$email) {
            return;
        }

        $notification = OrderNotification::create([
            'order_id' => $order->id,
            'channel' => 'email',
            'type' => $type,
            'recipient' => $email,
            'subject' => "[Pedido #{$order->order_number}] {$subject}",
            'content' => $this->getEmailContent($order, $type),
            'status' => 'pending',
        ]);

        try {
            // TODO: Implementar envio real de e-mail
            // Mail::to($email)->send(new OrderNotificationMail($order, $type));
            
            $notification->markAsSent();
            
            Log::info("Email notification sent", [
                'order_id' => $order->id,
                'type' => $type,
                'recipient' => $email,
            ]);
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            
            Log::error("Failed to send email notification", [
                'order_id' => $order->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Enviar notificação por WhatsApp
     */
    protected function sendWhatsAppNotification(ClientOrder $order, string $type): void
    {
        $phone = $order->customer_phone;
        
        if (!$phone) {
            return;
        }

        // Formatar número para WhatsApp (55 + DDD + número)
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) === 11) {
            $phone = '55' . $phone;
        } elseif (strlen($phone) === 10) {
            $phone = '55' . $phone;
        }

        $notification = OrderNotification::create([
            'order_id' => $order->id,
            'channel' => 'whatsapp',
            'type' => $type,
            'recipient' => $phone,
            'content' => $this->getWhatsAppContent($order, $type),
            'status' => 'pending',
        ]);

        try {
            // TODO: Implementar integração com API do WhatsApp Business
            // $this->whatsappApi->sendMessage($phone, $content);
            
            $notification->markAsSent();
            
            Log::info("WhatsApp notification sent", [
                'order_id' => $order->id,
                'type' => $type,
                'recipient' => $phone,
            ]);
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            
            Log::error("Failed to send WhatsApp notification", [
                'order_id' => $order->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Gerar conteúdo do e-mail
     */
    protected function getEmailContent(ClientOrder $order, string $type): string
    {
        $storeName = config('app.name', 'Vinil Store');
        
        return match ($type) {
            'order_created' => "Olá {$order->customer_name},\n\nRecebemos seu pedido #{$order->order_number}.\n\nValor total: {$order->formatted_total}\n\nAguardamos a confirmação do pagamento.\n\nAtenciosamente,\n{$storeName}",
            
            'payment_confirmed' => "Olá {$order->customer_name},\n\nO pagamento do seu pedido #{$order->order_number} foi confirmado!\n\nEstamos preparando seu pedido para envio.\n\nAtenciosamente,\n{$storeName}",
            
            'order_processing' => "Olá {$order->customer_name},\n\nSeu pedido #{$order->order_number} está sendo preparado para envio.\n\nEm breve você receberá o código de rastreio.\n\nAtenciosamente,\n{$storeName}",
            
            'order_shipped' => "Olá {$order->customer_name},\n\nSeu pedido #{$order->order_number} foi enviado!\n\nCódigo de rastreio: {$order->tracking_code}\nTransportadora: {$order->shipping_carrier}\n\nAtenciosamente,\n{$storeName}",
            
            'order_delivered' => "Olá {$order->customer_name},\n\nSeu pedido #{$order->order_number} foi entregue!\n\nEsperamos que você aproveite seus discos.\n\nAtenciosamente,\n{$storeName}",
            
            'order_cancelled' => "Olá {$order->customer_name},\n\nSeu pedido #{$order->order_number} foi cancelado.\n\nSe você tiver dúvidas, entre em contato conosco.\n\nAtenciosamente,\n{$storeName}",
            
            'tracking_update' => "Olá {$order->customer_name},\n\nAtualização do seu pedido #{$order->order_number}:\n\nCódigo de rastreio: {$order->tracking_code}\n\nAtenciosamente,\n{$storeName}",
            
            default => "Atualização do pedido #{$order->order_number}",
        };
    }

    /**
     * Gerar conteúdo do WhatsApp
     */
    protected function getWhatsAppContent(ClientOrder $order, string $type): string
    {
        $storeName = config('app.name', 'Vinil Store');
        
        return match ($type) {
            'order_created' => "🎵 *{$storeName}*\n\nOlá {$order->customer_name}!\n\nRecebemos seu pedido *#{$order->order_number}*\n💰 Total: {$order->formatted_total}\n\nAguardamos a confirmação do pagamento.",
            
            'payment_confirmed' => "🎵 *{$storeName}*\n\n✅ Pagamento confirmado!\n\nPedido *#{$order->order_number}*\n\nEstamos preparando seu pedido para envio.",
            
            'order_processing' => "🎵 *{$storeName}*\n\n📦 Pedido em preparação!\n\nPedido *#{$order->order_number}*\n\nEm breve você receberá o código de rastreio.",
            
            'order_shipped' => "🎵 *{$storeName}*\n\n🚚 Pedido enviado!\n\nPedido *#{$order->order_number}*\n📍 Rastreio: {$order->tracking_code}\n🏢 {$order->shipping_carrier}",
            
            'order_delivered' => "🎵 *{$storeName}*\n\n🎉 Pedido entregue!\n\nPedido *#{$order->order_number}*\n\nAproveite seus discos! 🎶",
            
            'order_cancelled' => "🎵 *{$storeName}*\n\n❌ Pedido cancelado\n\nPedido *#{$order->order_number}*\n\nDúvidas? Entre em contato.",
            
            'tracking_update' => "🎵 *{$storeName}*\n\n📍 Atualização de rastreio\n\nPedido *#{$order->order_number}*\nRastreio: {$order->tracking_code}",
            
            default => "Atualização do pedido #{$order->order_number}",
        };
    }
}
