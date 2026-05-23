<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Booking;

use Illuminate\Http\JsonResponse;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Resources\Booking\ScheduleResource;
use TitaKita\Services\Application\Handlers\Booking\DTO\ScheduleResultDTO;
use TitaKita\Services\Application\Handlers\Booking\GetScheduleHandler;

class GetScheduleAction extends BaseAction
{
    public function __construct(
        private readonly GetScheduleHandler $handler,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $schedule = $this->handler->handle($eventId);

        if ($schedule === null) {
            return $this->jsonResponse(['data' => null]);
        }

        $generatedSessionCount = $this->productRepository
            ->findWhere([ProductDomainObjectAbstract::SCHEDULE_ID => $schedule->getId()])
            ->count();

        return $this->resourceResponse(
            resource: ScheduleResource::class,
            data: new ScheduleResultDTO(
                schedule: $schedule,
                generatedSessionCount: $generatedSessionCount,
            ),
        );
    }
}
