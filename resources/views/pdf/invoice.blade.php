<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
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
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 4px;
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
            width: 85px;
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
            margin-top: 40px;
        }
        .signature-block {
            width: 300px;
            margin: 0 auto;
            text-align: center;
        }
        .signature-script {
            font-family: 'Times-Roman', 'DejaVu Serif', serif;
            font-style: italic;
            font-weight: bold;
            font-size: 24px;
            letter-spacing: 1px;
            color: #1a1a4e;
            margin-bottom: -6px;
        }
        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 10px;
        }
        .signature-line strong {
            display: block;
            font-size: 11px;
        }
        .legal-text {
            font-size: 9px;
            color: #666;
            margin-top: 25px;
            text-align: center;
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
            <h1>Invoice</h1>
            <p>RDV Discos &mdash; Comprovante de venda</p>
        </div>

        <div class="order-info">
            <strong>Pedido: #{{ $order->order_number }}</strong> |
            Invoice: {{ $invoice->invoice_number }} |
            Data: {{ $generatedAt }}
        </div>

        <div class="info-row">
            <div class="info-col">
                <div class="info-box">
                    <h3>Remetente</h3>
                    <p><span class="label">Nome:</span> {{ $invoice->sender_name }}</p>
                    <p><span class="label">Razão Social:</span> {{ $invoice->sender_legal_name }}</p>
                    <p><span class="label">CNPJ:</span> {{ $invoice->sender_cpf_cnpj }}</p>
                    @if($invoice->sender_ie)
                        <p><span class="label">Inscr. Estadual:</span> {{ $invoice->sender_ie }}</p>
                    @endif
                    <p><span class="label">Endereço:</span> {{ $invoice->sender_address }}</p>
                    <p><span class="label">Telefone:</span> {{ $invoice->sender_phone }}</p>
                    <p><span class="label">E-mail:</span> {{ $invoice->sender_email }}</p>
                </div>
            </div>
            <div class="info-col">
                <div class="info-box">
                    <h3>Destinatário</h3>
                    <p><span class="label">Nome:</span> {{ $invoice->recipient_name }}</p>
                    @if($invoice->recipient_cpf_cnpj)
                        <p><span class="label">CPF:</span> {{ $invoice->recipient_cpf_cnpj }}</p>
                    @endif
                    <p><span class="label">Endereço:</span> {{ $invoice->recipient_address }}</p>
                    @if($invoice->recipient_phone)
                        <p><span class="label">Telefone:</span> {{ $invoice->recipient_phone }}</p>
                    @endif
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50%">Descrição</th>
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
            <div class="signature-block">
                <div class="signature-script">RDV Discos</div>
                <div class="signature-line">
                    <strong>{{ $invoice->sender_legal_name ?? $invoice->sender_name }}</strong>
                    {{ $invoice->sender_name }} &mdash; CNPJ {{ $invoice->sender_cpf_cnpj }}
                </div>
            </div>

            <p class="legal-text">
                Documento de conferência e transporte referente ao pedido #{{ $order->order_number }}.
                Este documento não substitui nota fiscal eletrônica.
            </p>
        </div>
    </div>
</body>
</html>
