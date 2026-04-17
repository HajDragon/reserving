<?php

namespace App\Mail;

use App\Models\ReservationOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationPendingReviewMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public ReservationOrder $reservationOrder) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your order #'.$this->reservationOrder->id.' is under review',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservations.pending-review',
            with: [
                'reservationOrder' => $this->reservationOrder,
                'reservations' => $this->reservationOrder->reservations,
                'user' => $this->reservationOrder->user,
            ],
        );
    }
}
