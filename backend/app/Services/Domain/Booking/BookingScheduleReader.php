<?php

declare(strict_types=1);

namespace TitaKita\Services\Domain\Booking;

use Illuminate\Support\Collection;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\ScheduleDomainObjectAbstract;
use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\ScheduleDomainObject;
use TitaKita\Repository\Eloquent\Value\OrderAndDirection;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Repository\Interfaces\ScheduleRepositoryInterface;
use TitaKita\Services\Application\Handlers\Booking\DTO\BookingScheduleResultDTO;

class BookingScheduleReader
{
    public function __construct(
        private readonly ScheduleRepositoryInterface $scheduleRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function read(int $eventId): BookingScheduleResultDTO
    {
        /** @var ScheduleDomainObject|null $schedule */
        $schedule = $this->scheduleRepository->findFirstWhere([
            ScheduleDomainObjectAbstract::EVENT_ID => $eventId,
        ]);

        $sessions = $schedule === null
            ? new Collection
            : $this->productRepository
                ->loadRelation(ProductPriceDomainObject::class)
                ->loadRelation(ImageDomainObject::class)
                ->findWhere(
                    [ProductDomainObjectAbstract::SCHEDULE_ID => $schedule->getId()],
                    ['*'],
                    [new OrderAndDirection(
                        order: ProductDomainObjectAbstract::SESSION_START_AT,
                        direction: 'asc',
                    )],
                );

        return new BookingScheduleResultDTO(
            eventId: $eventId,
            schedule: $schedule,
            sessions: $sessions,
        );
    }
}
