<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EsimQrMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public ?string $qrContent,
        public array $providerResponse = [],
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your eSIM QR is ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.esim-qr',
        );
    }

    public function attachments(): array
    {
        $binary = $this->decodePng($this->qrContent);
        if (!$binary) {
            return [];
        }

        return [
            Attachment::fromData(fn () => $binary, 'esim-qr.png')
                ->withMime('image/png'),
        ];
    }

    private function decodePng(?string $content): ?string
    {
        if (!$content) {
            return null;
        }

        if (str_starts_with($content, 'data:image')) {
            $parts = explode(',', $content, 2);
            if (isset($parts[1])) {
                $content = $parts[1];
            }
        }

        $decoded = base64_decode($content, true);

        return $decoded !== false ? $decoded : null;
    }
}
