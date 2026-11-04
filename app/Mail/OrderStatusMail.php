<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param  'paid'|'failed'  $type */
    public function __construct(public Order $order, public string $type) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->type === 'paid'
                ? "Konfirmasi Pembayaran Tiket - Order #{$this->order->id}"
                : "Pembayaran Tiket Ditolak - Order #{$this->order->id}",
        );
    }

    public function content(): Content
    {
        return new Content(view: $this->type === 'paid' ? 'emails.order-confirmed' : 'emails.order-rejected');
    }
}
