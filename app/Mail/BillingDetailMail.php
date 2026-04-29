<?php

namespace App\Mail;

use App\Models\Billing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BillingDetailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Billing $billing) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Detalle de cobro — ' . $this->billing->concepto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.billing_detail',
        );
    }
}
