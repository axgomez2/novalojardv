# Configuração do Mercado Pago - Sandbox

Este guia explica como configurar o Mercado Pago para testes no ambiente sandbox.

## 1. Criar Conta de Desenvolvedor

1. Acesse: https://www.mercadopago.com.br/developers
2. Faça login com sua conta Mercado Pago (ou crie uma)
3. Aceite os termos de desenvolvedor

## 2. Criar Aplicação

1. No painel de desenvolvedor, vá em **Suas integrações**
2. Clique em **Criar aplicação**
3. Preencha:
   - **Nome**: Vinil Store (ou nome da sua loja)
   - **Modelo de integração**: Checkout Pro
   - **Produtos**: Checkout Pro, Pagamentos online
4. Clique em **Criar aplicação**

## 3. Obter Credenciais de Teste (Sandbox)

1. Na sua aplicação, vá em **Credenciais de teste**
2. Copie:
   - **Public Key**: Começa com `TEST-`
   - **Access Token**: Começa com `TEST-`

## 4. Configurar no .env

Adicione as credenciais no arquivo `.env`:

```env
# Mercado Pago (Sandbox)
MERCADO_PAGO_ACCESS_TOKEN=TEST-0000000000000000-000000-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-000000000
MERCADO_PAGO_PUBLIC_KEY=TEST-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

## 5. Criar Usuários de Teste

Para testar pagamentos, você precisa de usuários de teste:

1. No painel, vá em **Contas de teste**
2. Clique em **Criar conta de teste**
3. Crie 2 usuários:
   - **Vendedor**: Para receber pagamentos
   - **Comprador**: Para fazer pagamentos de teste

### Dados do Comprador de Teste

Use estes dados para testar pagamentos:

**Cartão de Crédito (Aprovado):**
- Número: `5031 4332 1540 6351`
- Validade: Qualquer data futura (ex: 11/25)
- CVV: `123`
- Nome: `APRO` (para aprovar) ou `OTHE` (para recusar)
- CPF: `12345678909`

**Cartão de Crédito (Recusado):**
- Número: `5031 4332 1540 6351`
- Validade: Qualquer data futura
- CVV: `123`
- Nome: `OTHE`

**PIX:**
- O PIX no sandbox gera um QR Code de teste
- Para simular aprovação, use a API de sandbox ou aguarde o webhook

## 6. Testar Webhook Localmente

Para testar webhooks em desenvolvimento local, use o ngrok:

```bash
# Instalar ngrok
npm install -g ngrok

# Expor sua aplicação
ngrok http 80

# Copie a URL gerada (ex: https://abc123.ngrok.io)
```

Configure a URL do webhook no Mercado Pago:
1. Vá em **Webhooks** na sua aplicação
2. Adicione: `https://sua-url-ngrok.io/api/webhooks/mercadopago`
3. Selecione os eventos: `payment`

## 7. Fluxo de Pagamento

### PIX
1. Cliente escolhe PIX no checkout
2. Sistema cria pagamento via API
3. Retorna QR Code e código copia-cola
4. Cliente paga pelo app do banco
5. Mercado Pago envia webhook
6. Sistema atualiza status do pedido

### Checkout Pro (Cartão)
1. Cliente escolhe Cartão no checkout
2. Sistema cria preferência de pagamento
3. Cliente é redirecionado para Mercado Pago
4. Cliente preenche dados do cartão
5. Após pagamento, retorna para:
   - `/pedido/{numero}/sucesso` - Aprovado
   - `/pedido/{numero}/pendente` - Pendente
   - `/pedido/{numero}/erro` - Recusado

## 8. URLs de Retorno

Configure no `.env`:

```env
FRONTEND_URL=http://localhost:3000
```

As URLs de retorno são:
- **Sucesso**: `{FRONTEND_URL}/pedido/{order_number}/sucesso`
- **Pendente**: `{FRONTEND_URL}/pedido/{order_number}/pendente`
- **Erro**: `{FRONTEND_URL}/pedido/{order_number}/erro`

## 9. Simular Pagamento PIX no Sandbox

Para simular um pagamento PIX aprovado no sandbox:

```bash
curl -X PUT \
  'https://api.mercadopago.com/v1/payments/{payment_id}' \
  -H 'Authorization: Bearer TEST-SEU-ACCESS-TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{"status": "approved"}'
```

Substitua `{payment_id}` pelo ID do pagamento retornado na criação.

## 10. Verificar Logs

Os logs de pagamento ficam em:
- Laravel: `storage/logs/laravel.log`
- Busque por: `Mercado Pago`

## Troubleshooting

### Erro "Token não configurado"
- Verifique se as variáveis estão no `.env`
- Execute `php artisan config:clear`

### Webhook não chega
- Verifique se a URL está acessível externamente
- Use ngrok para desenvolvimento local
- Verifique os logs do Mercado Pago

### PIX não gera QR Code
- Verifique se o Access Token é de teste (começa com TEST-)
- Verifique se o email do pagador é válido

## Referências

- [Documentação Oficial](https://www.mercadopago.com.br/developers/pt/docs)
- [API Reference](https://www.mercadopago.com.br/developers/pt/reference)
- [Credenciais de Teste](https://www.mercadopago.com.br/developers/panel/app)
