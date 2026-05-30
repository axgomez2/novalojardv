<?php

namespace App\Mail;

use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Observação: NÃO implementa ShouldQueue de propósito — envio síncrono
// para funcionar mesmo sem queue worker em produção.
class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $verifyUrl;
    public string $userName;
    public ?string $logoUrl;
    public string $siteName;

    public function __construct(string $code, string $userName)
    {
        $this->code = $code;
        $this->userName = $userName;

        $frontend = rtrim(config('app.frontend_url', env('FRONTEND_URL', config('app.url'))), '/');
        $this->verifyUrl = $frontend . '/verify-email?code=' . urlencode($code);

        // Logo do site (transformar em URL absoluta para clientes de email)
        $logo = SiteSetting::get('logo');
        if ($logo && !preg_match('#^https?://#i', $logo)) {
            $logo = rtrim(config('app.url'), '/') . '/' . ltrim($logo, '/');
        }
        $this->logoUrl = $logo ?: null;
        $this->siteName = SiteSetting::get('site_name', 'RDV Discos');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifique seu e-mail · RDV Discos',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email',
            with: [
                'code' => $this->code,
                'verifyUrl' => $this->verifyUrl,
                'userName' => $this->userName,
                'logoUrl' => $this->logoUrl,
                'siteName' => $this->siteName,
            ],
        );
    }
}
