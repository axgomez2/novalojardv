<x-mail::message>
# Pedido Entregue!

Olá, **{{ $order->customer_name }}**!

Seu pedido **#{{ $order->order_number }}** foi entregue com sucesso!

Esperamos que você aproveite muito seus discos!

---

## Itens Recebidos

<x-mail::table>
| Disco | Qtd |
|:------|:---:|
@foreach($items as $item)
| {{ $item->vinylStock?->vinylMaster?->title ?? 'Item' }} - {{ $item->vinylStock?->vinylMaster?->artist_names ?? '' }} | {{ $item->quantity }} |
@endforeach
</x-mail::table>

---

## Teve algum problema?

Se houver qualquer problema com seu pedido ou com os produtos recebidos, não hesite em entrar em contato conosco. Estamos aqui para ajudar!

<x-mail::button :url="'https://wa.me/5511947159293?text=Olá! Preciso de ajuda com o pedido ' . $order->order_number">
Falar no WhatsApp
</x-mail::button>

---

## Avalie sua Experiência

Sua opinião é muito importante para nós! Se puder, deixe uma avaliação sobre sua experiência de compra.

---

Obrigado pela confiança e por escolher a **RDV Discos**!

Esperamos vê-lo novamente em breve.

Atenciosamente,  
Equipe RDV Discos

<x-mail::subcopy>
Siga-nos nas redes sociais para ficar por dentro das novidades e lançamentos!
</x-mail::subcopy>
</x-mail::message>
