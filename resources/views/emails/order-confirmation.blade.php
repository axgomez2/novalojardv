<x-mail::message>
# Pedido Confirmado!

Olá, **{{ $order->clientUser->name }}**!

Seu pedido foi confirmado e está sendo processado.

---

## Detalhes do Pedido

**Pedido:** #{{ $order->order_number }}  
**Data:** {{ $order->created_at->format('d/m/Y H:i') }}

---

## Itens

<x-mail::table>
| Disco | Qtd | Valor |
|:------|:---:|------:|
@foreach($items as $item)
| {{ $item->vinylStock?->vinylMaster?->title ?? 'Item' }} - {{ $item->vinylStock?->vinylMaster?->artist_names ?? '' }} | {{ $item->quantity }} | R$ {{ number_format($item->total_price, 2, ',', '.') }} |
@endforeach
</x-mail::table>

---

## Resumo

| | |
|:---|---:|
| **Subtotal** | R$ {{ number_format($order->subtotal, 2, ',', '.') }} |
| **Frete ({{ $order->shipping_service_name }})** | R$ {{ number_format($order->shipping_cost, 2, ',', '.') }} |
@if($order->discount > 0)
| **Desconto** | - R$ {{ number_format($order->discount, 2, ',', '.') }} |
@endif
| **Total** | **R$ {{ number_format($order->total, 2, ',', '.') }}** |

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

Você pode acompanhar seu pedido acessando sua conta no site.

<x-mail::button :url="config('app.frontend_url') . '/minha-conta/pedidos'">
Ver Meus Pedidos
</x-mail::button>

Obrigado por comprar na **RDV Discos**!

Atenciosamente,  
Equipe RDV Discos
</x-mail::message>
