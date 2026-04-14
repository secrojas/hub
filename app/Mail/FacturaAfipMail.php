<?php

namespace App\Mail;

use App\Models\Billing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FacturaAfipMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Billing $billing,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu factura AFIP — ' . $this->billing->concepto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.factura_afip',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('local', $this->billing->afip_pdf_path)
                ->as('factura-afip.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
