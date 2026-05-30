<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifique seu e-mail · RDV Discos</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f5f5f4; color: #1c1917;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f4;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border: 1px solid #e7e5e4; border-radius: 16px; overflow: hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="padding: 32px 40px 16px; text-align: center; border-bottom: 1px solid #f5f5f4;">
                            <table role="presentation" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <td style="vertical-align: middle; padding-right: 12px;">
                                        <div style="width: 44px; height: 44px; background-color: #facc15; border-radius: 50%; text-align: center; line-height: 44px; font-size: 22px;">
                                            🎵
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <span style="font-size: 24px; font-weight: 700; color: #1c1917; letter-spacing: -0.02em;">RDV Discos</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 40px 24px;">
                            <h1 style="color: #1c1917; font-size: 22px; font-weight: 700; margin: 0 0 16px; text-align: center; letter-spacing: -0.01em;">
                                Confirme seu e-mail
                            </h1>

                            <p style="color: #44403c; font-size: 16px; line-height: 1.6; margin: 0 0 8px; text-align: center;">
                                Olá, <strong style="color: #1c1917;">{{ $userName }}</strong>!
                            </p>

                            <p style="color: #57534e; font-size: 15px; line-height: 1.6; margin: 0 0 32px; text-align: center;">
                                Para ativar sua conta na <strong>RDV Discos</strong>, clique no botão abaixo ou utilize o código de verificação.
                            </p>

                            <!-- Botão CTA -->
                            <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto 32px;">
                                <tr>
                                    <td style="border-radius: 10px; background-color: #facc15;">
                                        <a href="{{ $verifyUrl }}"
                                           style="display: inline-block; padding: 14px 32px; font-size: 16px; font-weight: 600; color: #1c1917; text-decoration: none; border-radius: 10px;">
                                            Verificar meu e-mail
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
                                <tr>
                                    <td style="border-top: 1px solid #e7e5e4; line-height: 1px; height: 1px;">&nbsp;</td>
                                    <td style="padding: 0 12px; color: #a8a29e; font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em; white-space: nowrap;">
                                        ou use o código
                                    </td>
                                    <td style="border-top: 1px solid #e7e5e4; line-height: 1px; height: 1px;">&nbsp;</td>
                                </tr>
                            </table>

                            <!-- Code Box -->
                            <div style="background-color: #fafaf9; border: 1px solid #e7e5e4; border-radius: 12px; padding: 24px; text-align: center; margin: 0 0 24px;">
                                <div style="font-size: 12px; color: #78716c; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 8px;">
                                    Código de verificação
                                </div>
                                <div style="font-size: 36px; font-weight: 700; letter-spacing: 12px; color: #1c1917; font-family: 'SF Mono', Menlo, Monaco, Consolas, monospace;">
                                    {{ $code }}
                                </div>
                            </div>

                            <p style="color: #78716c; font-size: 13px; line-height: 1.6; margin: 0 0 8px; text-align: center;">
                                Este código expira em <strong>30 minutos</strong>.
                            </p>

                            <p style="color: #a8a29e; font-size: 13px; line-height: 1.6; margin: 24px 0 0; text-align: center;">
                                Se você não criou uma conta na RDV Discos, ignore este e-mail com segurança.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 40px; background-color: #fafaf9; border-top: 1px solid #f5f5f4; text-align: center;">
                            <p style="color: #78716c; font-size: 12px; line-height: 1.6; margin: 0 0 4px;">
                                © {{ date('Y') }} RDV Discos · Todos os direitos reservados.
                            </p>
                            <p style="color: #a8a29e; font-size: 12px; line-height: 1.6; margin: 0;">
                                Dúvidas? Responda este e-mail ou escreva para
                                <a href="mailto:contato@rdvdiscos.com.br" style="color: #ca8a04; text-decoration: none;">contato@rdvdiscos.com.br</a>
                            </p>
                        </td>
                    </tr>
                </table>

                <p style="color: #a8a29e; font-size: 11px; margin: 16px 0 0; text-align: center;">
                    Não consegue clicar no botão? Copie e cole este link no navegador:<br>
                    <a href="{{ $verifyUrl }}" style="color: #78716c; word-break: break-all;">{{ $verifyUrl }}</a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
