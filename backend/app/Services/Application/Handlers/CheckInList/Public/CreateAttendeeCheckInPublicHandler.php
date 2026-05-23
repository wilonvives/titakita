<?php

namespace TitaKita\Services\Application\Handlers\CheckInList\Public;

use TitaKita\DomainObjects\AttendeeCheckInDomainObject;
use TitaKita\Exceptions\CannotCheckInException;
use TitaKita\Services\Application\Handlers\CheckInList\Public\DTO\CreateAttendeeCheckInPublicDTO;
use TitaKita\Services\Domain\CheckInList\CreateAttendeeCheckInService;
use TitaKita\Services\Domain\CheckInList\DTO\CreateAttendeeCheckInsResponseDTO;
use TitaKita\Services\Infrastructure\DomainEvents\DomainEventDispatcherService;
use TitaKita\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use TitaKita\Services\Infrastructure\DomainEvents\Events\CheckinEvent;
use Psr\Log\LoggerInterface;
use Throwable;

class CreateAttendeeCheckInPublicHandler
{
    public function __construct(
        private readonly CreateAttendeeCheckInService $createAttendeeCheckInService,
        private readonly LoggerInterface              $logger,
        private readonly DomainEventDispatcherService $domainEventDispatcherService,
    )
    {
    }

    /**
     * @throws CannotCheckInException|Throwable
     */
    public function handle(CreateAttendeeCheckInPublicDTO $checkInData): CreateAttendeeCheckInsResponseDTO
    {
        $checkIns = $this->createAttendeeCheckInService->checkInAttendees(
            $checkInData->checkInListUuid,
            $checkInData->checkInUserIpAddress,
            $checkInData->attendeesAndActions,
        );

        $this->logger->info('Attendee check-ins created', [
            'attendee_ids' => $checkIns->attendeeCheckIns
                ->map(fn(AttendeeCheckInDomainObject $checkIn) => $checkIn->getAttendeeId())->toArray(),
            'check_in_list_uuid' => $checkInData->checkInListUuid,
            'ip_address' => $checkInData->checkInUserIpAddress,
        ]);

        /** @var AttendeeCheckInDomainObject $checkIn */
        foreach ($checkIns->attendeeCheckIns as $checkIn) {
            $this->domainEventDispatcherService->dispatch(
                new CheckinEvent(
                    type: DomainEventType::CHECKIN_CREATED,
                    attendeeCheckinId: $checkIn->getId(),
                )
            );
        }

        return $checkIns;
    }
}
