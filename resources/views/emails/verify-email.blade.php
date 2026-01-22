<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifique seu e-mail</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #1c1917;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #292524; border-radius: 16px; overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; background-color: #292524;">
                            <div style="display: inline-flex; align-items: center; gap: 12px;">
                                <div style="width: 48px; height: 48px; background-color: #facc15; border-radius: 50%; display: inline-block; text-align: center; line-height: 48px;">
                                    <span style="font-size: 24px;">🎵</span>
                                </div>
                                <span style="font-size: 28px; font-weight: bold; color: #ffffff;">Vinil Store</span>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 20px 40px 40px;">
                            <h1 style="color: #ffffff; font-size: 24px; margin: 0 0 16px; text-align: center;">
                                Verifique seu e-mail
                            </h1>
                            
                            <p style="color: #a8a29e; font-size: 16px; line-height: 1.6; margin: 0 0 24px; text-align: center;">
                                Olá, <strong style="color: #ffffff;">{{ $user->name }}</strong>!
                            </p>
                            
                            <p style="color: #a8a29e; font-size: 16px; line-height: 1.6; margin: 0 0 32px; text-align: center;">
                                Use o código abaixo para verificar seu e-mail e ativar sua conta:
                            </p>
                            
                            <!-- Code Box -->
                            <div style="background-color: #1c1917; border-radius: 12px; padding: 24px; text-align: center; margin-bottom: 32px;">
                                <span style="font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #facc15; font-family: monospace;">
                                    {{ $code }}
                                </span>
                            </div>
                            
                            <p style="color: #78716c; font-size: 14px; line-height: 1.6; margin: 0 0 8px; text-align: center;">
                                Este código expira em <strong>30 minutos</strong>.
                            </p>
                            
                            <p style="color: #78716c; font-size: 14px; line-height: 1.6; margin: 0; text-align: center;">
                                Se você não criou uma conta na Vinil Store, ignore este e-mail.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 40px; background-color: #1c1917; border-top: 1px solid #44403c;">
                            <p style="color: #78716c; font-size: 12px; line-height: 1.6; margin: 0; text-align: center;">
                                © {{ date('Y') }} Vinil Store. Todos os direitos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
