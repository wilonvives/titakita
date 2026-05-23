<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking;

use TitaKita\DomainObjects\Generated\ScheduleDomainObjectAbstract;
use TitaKita\DomainObjects\ScheduleDomainObject;
use TitaKita\Repository\Interfaces\ScheduleRepositoryInterface;

class GetScheduleHandler
{
    public function __construct(
        private readonly ScheduleRepositoryInterface $scheduleRepository,
    ) {}

    public function handle(int $eventId): ?ScheduleDomainObject
    {
        /** @var ScheduleDomainObject|null $schedule */
        $schedule = $this->scheduleRepository->findFirstWhere([
            ScheduleDomainObjectAbstract::EVENT_ID => $eventId,
        ]);

        return $schedule;
    }
}
