<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Declaração de Conteúdo - {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .info-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }
        .info-col:last-child {
            padding-right: 0;
            padding-left: 15px;
        }
        .info-box {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 15px;
        }
        .info-box h3 {
            font-size: 11px;
            text-transform: uppercase;
            background: #f5f5f5;
            margin: -10px -10px 10px -10px;
            padding: 5px 10px;
            border-bottom: 1px solid #ccc;
        }
        .info-box p {
            margin-bottom: 3px;
        }
        .info-box .label {
            font-weight: bold;
            display: inline-block;
            width: 80px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }
        table th {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        table td {
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            width: 250px;
            margin-left: auto;
        }
        .totals td {
            padding: 4px 8px;
        }
        .totals .total-row {
            font-weight: bold;
            font-size: 12px;
            background: #f5f5f5;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        .signature-row {
            display: table;
            width: 100%;
            margin-top: 40px;
        }
        .signature-col {
            display: table-cell;
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 40px;
        }
        .legal-text {
            font-size: 9px;
            color: #666;
            margin-top: 20px;
            text-align: justify;
        }
        .order-info {
            background: #f9f9f9;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }
        .order-info strong {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Declaração de Conteúdo</h1>
            <p>Para fins de postagem conforme Art. 1º da Portaria nº 6.014/2017 ECT</p>
        </div>

        <div class="order-info">
            <strong>Pedido: #{{ $order->order_number }}</strong> | 
            Declaração: {{ $invoice->invoice_number }} | 
            Data: {{ $generatedAt }}
        </div>

        <div class="info-row">
            <div class="info-col">
                <div class="info-box">
                    <h3>Remetente</h3>
                    <p><span class="label">Nome:</span> {{ $invoice->sender_name }}</p>
                    <p><span class="label">CPF/CNPJ:</span> {{ $invoice->sender_cpf_cnpj }}</p>
                    <p><span class="label">Endereço:</span> {{ $invoice->sender_address }}</p>
                </div>
            </div>
            <div class="info-col">
                <div class="info-box">
                    <h3>Destinatário</h3>
                    <p><span class="label">Nome:</span> {{ $invoice->recipient_name }}</p>
                    @if($invoice->recipient_cpf_cnpj)
                        <p><span class="label">CPF/CNPJ:</span> {{ $invoice->recipient_cpf_cnpj }}</p>
                    @endif
                    <p><span class="label">Endereço:</span> {{ $invoice->recipient_address }}</p>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50%">Descrição do Conteúdo</th>
                    <th class="text-center" style="width: 15%">Quantidade</th>
                    <th class="text-right" style="width: 17%">Valor Unit.</th>
                    <th class="text-right" style="width: 18%">Valor Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item['description'] }}</td>
                        <td class="text-center">{{ $item['quantity'] }}</td>
                        <td class="text-right">R$ {{ number_format($item['unit_value'], 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($item['total_value'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">R$ {{ number_format($invoice->total_value, 2, ',', '.') }}</td>
            </tr>
            @if($invoice->shipping_value > 0)
                <tr>
                    <td>Frete:</td>
                    <td class="text-right">R$ {{ number_format($invoice->shipping_value, 2, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL:</td>
                <td class="text-right">R$ {{ number_format($invoice->total_value + $invoice->shipping_value, 2, ',', '.') }}</td>
            </tr>
        </table>

        <div class="footer">
            <p><strong>DECLARO</strong> que não me enquadro no conceito de contribuinte previsto no art. 4º da Lei Complementar nº 87/1996, uma vez que não realizo, com habitualidade ou em volume que caracterize intuito comercial, operações de circulação de mercadoria, ainda que se iniciem no exterior, ou estou dispensado da emissão da nota fiscal por força da legislação tributária vigente, responsabilizando-me, nos termos da lei e sob as penas da lei, por informações inverídicas.</p>
            
            <div class="signature-row">
                <div class="signature-col">
                    <div class="signature-line">
                        Assinatura do Remetente
                    </div>
                </div>
                <div class="signature-col" style="width: 10%"></div>
                <div class="signature-col">
                    <div class="signature-line">
                        Assinatura do Destinatário
                    </div>
                </div>
            </div>

            <p class="legal-text">
                Este documento é uma declaração de conteúdo para fins de transporte postal, conforme estabelecido pela Empresa Brasileira de Correios e Telégrafos (ECT). 
                O remetente declara que o conteúdo descrito acima corresponde fielmente aos itens enviados e assume total responsabilidade pelas informações prestadas.
            </p>
        </div>
    </div>
</body>
</html>
