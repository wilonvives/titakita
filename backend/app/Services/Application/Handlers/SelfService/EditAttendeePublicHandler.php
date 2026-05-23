<?php

namespace TitaKita\Services\Application\Handlers\SelfService;

use TitaKita\DomainObjects\Generated\AttendeeDomainObjectAbstract;
use TitaKita\Exceptions\SelfServiceDisabledException;
use TitaKita\Repository\Interfaces\AttendeeRepositoryInterface;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Services\Application\Handlers\SelfService\DTO\EditAttendeePublicDTO;
use TitaKita\Services\Domain\SelfService\DTO\EditAttendeeResultDTO;
use TitaKita\Services\Domain\SelfService\SelfServiceEditAttendeeService;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class EditAttendeePublicHandler
{
    use SelfServiceValidationTrait;

    public function __construct(
        private readonly AttendeeRepositoryInterface $attendeeRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly SelfServiceEditAttendeeService $selfServiceEditAttendeeService,
    ) {
    }

    /**
     * @throws SelfServiceDisabledException
     */
    public function handle(EditAttendeePublicDTO $dto): EditAttendeeResultDTO
    {
        $this->loadAndValidateEvent($dto->eventId);
        $order = $this->loadAndValidateOrder($dto->orderShortId, $dto->eventId);

        $attendee = $this->attendeeRepository->findFirstWhere([
            AttendeeDomainObjectAbstract::SHORT_ID => $dto->attendeeShortId,
            AttendeeDomainObjectAbstract::ORDER_ID => $order->getId(),
            AttendeeDomainObjectAbstract::EVENT_ID => $dto->eventId,
        ]);

        if (!$attendee) {
            throw new ResourceNotFoundException(__('Attendee not found'));
        }

        return $this->selfServiceEditAttendeeService->editAttendee(
            attendee: $attendee,
            firstName: $dto->firstName,
            lastName: $dto->lastName,
            email: $dto->email,
            ipAddress: $dto->ipAddress,
            userAgent: $dto->userAgent
        );
    }
}
