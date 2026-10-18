<x-mail::message>
# Seu Pedido Foi Enviado!

Olá, **{{ $order->customer_name }}**!

Temos uma ótima notícia: seu pedido foi coletado pela transportadora e está a caminho!

---

## Informações de Envio

**Pedido:** #{{ $order->order_number }}  
**Transportadora:** {{ $order->shipping_carrier ?? 'Não informada' }}  
**Código de Rastreio:** {{ $order->tracking_code ?? 'Não disponível' }}

@if($trackingUrl)
<x-mail::button :url="$trackingUrl">
Rastrear Pedido
</x-mail::button>
@endif

---

## Itens do Pedido

<x-mail::table>
| Disco | Qtd |
|:------|:---:|
@foreach($items as $item)
| {{ $item->vinylStock?->vinylMaster?->title ?? 'Item' }} - {{ $item->vinylStock?->vinylMaster?->artist_names ?? '' }} | {{ $item->quantity }} |
@endforeach
</x-mail::table>

---

## Endereço de Entrega

{{ $address->street }}, {{ $address->number }}  
@if($address->complement)
{{ $address->complement }}  
@endif
{{ $address->neighborhood }}  
{{ $address->city }} - {{ $address->state }}  
CEP: {{ $address->zip_code }}

---

@if($order->admin_notes)
## Observações

{{ $order->admin_notes }}

---
@endif

Você também pode acompanhar seu pedido na sua conta:

<x-mail::button :url="config('app.frontend_url') . '/minha-conta/pedidos'">
Ver Meus Pedidos
</x-mail::button>

Obrigado por comprar na **RDV Discos**!

Atenciosamente,  
Equipe RDV Discos
</x-mail::message>
