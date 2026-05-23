<?php

namespace TitaKita\Mail\Attendee;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Mail\BaseMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * @uses /backend/resources/views/emails/orders/attendee-details-changed.blade.php
 */
class AttendeeDetailsChangedMail extends BaseMail
{
    public function __construct(
        private readonly string $ticketTitle,
        private readonly EventDomainObject $event,
        private readonly OrganizerDomainObject $organizer,
        private readonly EventSettingDomainObject $eventSettings,
        private readonly array $changedFields,
    ) {
        parent::__construct();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->eventSettings->getSupportEmail(),
            subject: __('Your Ticket Details Have Been Changed'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.attendee-details-changed',
            with: [
                'ticketTitle' => $this->ticketTitle,
                'event' => $this->event,
                'organizer' => $this->organizer,
                'eventSettings' => $this->eventSettings,
                'changedFields' => $this->changedFields,
            ]
        );
    }
}
