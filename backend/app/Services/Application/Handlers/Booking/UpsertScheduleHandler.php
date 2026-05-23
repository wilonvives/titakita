<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking;

use Illuminate\Database\DatabaseManager;
use Throwable;
use TitaKita\DomainObjects\Enums\EventType;
use TitaKita\DomainObjects\Generated\EventDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\ScheduleDomainObjectAbstract;
use TitaKita\DomainObjects\ScheduleDomainObject;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Repository\Interfaces\ScheduleRepositoryInterface;
use TitaKita\Services\Application\Handlers\Booking\DTO\ScheduleResultDTO;
use TitaKita\Services\Application\Handlers\Booking\DTO\UpsertScheduleDTO;
use TitaKita\Services\Domain\Booking\ScheduleGenerationService;

class UpsertScheduleHandler
{
    public function __construct(
        private readonly ScheduleRepositoryInterface $scheduleRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ScheduleGenerationService $scheduleGenerationService,
        private readonly DatabaseManager $databaseManager,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(UpsertScheduleDTO $dto): ScheduleResultDTO
    {
        return $this->databaseManager->transaction(function () use ($dto): ScheduleResultDTO {
            $attributes = [
                ScheduleDomainObjectAbstract::EVENT_ID => $dto->event_id,
                ScheduleDomainObjectAbstract::SESSION_DURATION_MINUTES => $dto->session_duration_minutes,
                ScheduleDomainObjectAbstract::START_TIMES => $dto->start_times,
                ScheduleDomainObjectAbstract::CAPACITY_PER_SESSION => $dto->capacity_per_session,
                ScheduleDomainObjectAbstract::SCOPE_TYPE => $dto->scope_type->value,
                ScheduleDomainObjectAbstract::WEEKDAYS => $dto->weekdays,
                ScheduleDomainObjectAbstract::RANGE_START_DATE => $dto->range_start_date,
                ScheduleDomainObjectAbstract::RANGE_END_DATE => $dto->range_end_date,
                ScheduleDomainObjectAbstract::SPECIFIC_DATES => $dto->specific_dates,
            ];

            $existing = $this->scheduleRepository->findFirstWhere([
                ScheduleDomainObjectAbstract::EVENT_ID => $dto->event_id,
            ]);

            if ($existing !== null) {
                $this->scheduleRepository->updateFromArray($existing->getId(), $attributes);
                $scheduleId = $existing->getId();
            } else {
                $scheduleId = $this->scheduleRepository->create($attributes)->getId();
            }

            /** @var ScheduleDomainObject $schedule */
            $schedule = $this->scheduleRepository->findById($scheduleId);

            $this->eventRepository->updateFromArray($dto->event_id, [
                EventDomainObjectAbstract::EVENT_TYPE => EventType::BOOKING->value,
            ]);

            $this->scheduleGenerationService->generate($schedule);

            $generatedSessionCount = $this->productRepository
                ->findWhere([ProductDomainObjectAbstract::SCHEDULE_ID => $schedule->getId()])
                ->count();

            return new ScheduleResultDTO(
                schedule: $schedule,
                generatedSessionCount: $generatedSessionCount,
            );
        });
    }
}
