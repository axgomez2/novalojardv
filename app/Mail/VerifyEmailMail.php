<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $code;
    public string $verifyUrl;
    public string $userName;

    public function __construct(string $code, string $userName)
    {
        $this->code = $code;
        $this->userName = $userName;

        $frontend = rtrim(config('app.frontend_url', env('FRONTEND_URL', config('app.url'))), '/');
        $this->verifyUrl = $frontend . '/verify-email?code=' . urlencode($code);
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
            ],
        );
    }
}
