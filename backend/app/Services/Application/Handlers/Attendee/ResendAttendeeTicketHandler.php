<?php

namespace TitaKita\Services\Application\Handlers\Attendee;

use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\DomainObjects\Status\AttendeeStatus;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\AttendeeRepositoryInterface;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Services\Application\Handlers\Attendee\DTO\ResendAttendeeTicketDTO;
use TitaKita\Services\Domain\Attendee\SendAttendeeTicketService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

readonly class ResendAttendeeTicketHandler
{
    public function __construct(
        private SendAttendeeTicketService   $sendAttendeeProductService,
        private AttendeeRepositoryInterface $attendeeRepository,
        private EventRepositoryInterface    $eventRepository,
        private LoggerInterface             $logger,
    )
    {
    }

    /**
     * @throws ResourceConflictException
     */
    public function handle(ResendAttendeeTicketDTO $resendAttendeeProductDTO): void
    {
        $attendee = $this->attendeeRepository
            ->loadRelation(new Relationship(OrderDomainObject::class, nested: [
                new Relationship(OrderItemDomainObject::class),
            ], name: 'order'))
            ->findFirstWhere([
                'id' => $resendAttendeeProductDTO->attendeeId,
                'event_id' => $resendAttendeeProductDTO->eventId,
            ]);

        if (!$attendee) {
            throw new ResourceNotFoundException();
        }

        if ($attendee->getStatus() !== AttendeeStatus::ACTIVE->name) {
            throw new ResourceConflictException('You cannot resend the ticket of an inactive attendee');
        }

        $event = $this->eventRepository
            ->loadRelation(new Relationship(OrganizerDomainObject::class, name: 'organizer'))
            ->loadRelation(EventSettingDomainObject::class)
            ->findById($resendAttendeeProductDTO->eventId);

        $this->sendAttendeeProductService->send(
            order: $attendee->getOrder(),
            attendee: $attendee,
            event: $event,
            eventSettings: $event->getEventSettings(),
            organizer: $event->getOrganizer(),
        );

        $this->logger->info('Attendee ticket resent', [
            'attendeeId' => $resendAttendeeProductDTO->attendeeId,
            'eventId' => $resendAttendeeProductDTO->eventId
        ]);
    }
}
