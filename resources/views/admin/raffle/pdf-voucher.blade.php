<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Números do Sorteio - RDV Discos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f59e0b;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #1c1917;
            margin-bottom: 5px;
        }
        .logo span {
            color: #f59e0b;
        }
        .subtitle {
            font-size: 16px;
            color: #666;
        }
        .client-info {
            background: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .client-info h2 {
            font-size: 14px;
            color: #92400e;
            margin-bottom: 5px;
        }
        .client-info p {
            font-size: 16px;
            font-weight: bold;
            color: #1c1917;
        }
        .client-info .email {
            font-size: 12px;
            font-weight: normal;
            color: #666;
        }
        .numbers-section {
            margin-bottom: 30px;
        }
        .numbers-section h3 {
            font-size: 14px;
            color: #1c1917;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e5e5;
        }
        .numbers-grid {
            display: table;
            width: 100%;
        }
        .number-row {
            display: table-row;
        }
        .number-box {
            display: table-cell;
            width: 16.66%;
            padding: 8px;
            text-align: center;
        }
        .number-box .number {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 15px 10px;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #92400e;
        }
        .summary {
            background: #f5f5f4;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .summary-label {
            color: #666;
        }
        .summary-value {
            font-weight: bold;
            color: #1c1917;
        }
        .acceptance {
            background: #dcfce7;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 11px;
            color: #166534;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        .footer p {
            margin-bottom: 5px;
        }
        .promo-info {
            background: #1c1917;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .promo-info h2 {
            font-size: 16px;
            color: #f59e0b;
            margin-bottom: 5px;
        }
        .promo-info p {
            font-size: 11px;
            color: #a8a29e;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">RDV <span>DISCOS</span></div>
        <div class="subtitle">Voucher de Participação - Sorteio 2026</div>
    </div>

    <div class="promo-info">
        <h2>{{ $settings->name }}</h2>
        <p>
            Período: {{ $settings->start_date?->format('d/m/Y') }} a {{ $settings->end_date?->format('d/m/Y') }}
            | Sorteio: {{ $settings->draw_date?->format('d/m/Y') }}
        </p>
    </div>

    <div class="client-info">
        <h2>PARTICIPANTE</h2>
        <p>{{ $client->name }}</p>
        <p class="email">{{ $client->email }}</p>
    </div>

    @if($acceptance && $acceptance->accepted)
        <div class="acceptance">
            ✓ Termos da promoção aceitos em {{ $acceptance->accepted_at->format('d/m/Y \à\s H:i') }}
        </div>
    @endif

    <div class="summary">
        <table width="100%">
            <tr>
                <td><span class="summary-label">Total de Números:</span></td>
                <td align="right"><span class="summary-value">{{ $numbers->count() }}</span></td>
            </tr>
            <tr>
                <td><span class="summary-label">Números Ativos:</span></td>
                <td align="right"><span class="summary-value">{{ $numbers->where('status', 'active')->count() }}</span></td>
            </tr>
            <tr>
                <td><span class="summary-label">Gerado em:</span></td>
                <td align="right"><span class="summary-value">{{ now()->format('d/m/Y H:i') }}</span></td>
            </tr>
        </table>
    </div>

    <div class="numbers-section">
        <h3>SEUS NÚMEROS DA SORTE</h3>
        <table width="100%" cellpadding="5">
            @foreach($numbers->chunk(6) as $chunk)
                <tr>
                    @foreach($chunk as $number)
                        <td width="16.66%" align="center">
                            <div class="number-box">
                                <div class="number">{{ $number->formatted_number }}</div>
                            </div>
                        </td>
                    @endforeach
                    @for($i = $chunk->count(); $i < 6; $i++)
                        <td width="16.66%"></td>
                    @endfor
                </tr>
            @endforeach
        </table>
    </div>

    <div class="footer">
        <p><strong>RDV Discos</strong> - A melhor loja de discos de vinil do Brasil</p>
        <p>www.rdvdiscos.com.br | contato@rdvdiscos.com.br</p>
        <p>Este documento é válido como comprovante de participação na promoção.</p>
        <p>Guarde seus números com cuidado. Boa sorte!</p>
    </div>
</body>
</html>
