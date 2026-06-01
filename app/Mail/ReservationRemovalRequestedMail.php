<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\ReservationRemovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationRemovalRequestedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public ReservationRemovalRequest $removalRequest,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Removal request for reservation #'.$this->reservation->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservations.removal-requested',
            with: [
                'reservation' => $this->reservation,
                'removalRequest' => $this->removalRequest,
                'reservationOrder' => $this->reservation->reservationOrder,
                'user' => $this->reservation->user,
            ],
        );
    }
}
