<?php

namespace TitaKita\Services\Domain\Attendee;

use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Services\Domain\Email\MailBuilderService;
use Illuminate\Contracts\Mail\Mailer;

class SendAttendeeTicketService
{
    public function __construct(
        private readonly Mailer             $mailer,
        private readonly MailBuilderService $mailBuilderService,
    )
    {
    }

    public function send(
        OrderDomainObject        $order,
        AttendeeDomainObject     $attendee,
        EventDomainObject        $event,
        EventSettingDomainObject $eventSettings,
        OrganizerDomainObject    $organizer,
    ): void
    {
        $mail = $this->mailBuilderService->buildAttendeeTicketMail(
            $attendee,
            $order,
            $event,
            $eventSettings,
            $organizer
        );

        $this->mailer
            ->to($attendee->getEmail())
            ->locale($attendee->getLocale())
            ->send($mail);
    }
}
