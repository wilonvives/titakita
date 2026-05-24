<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Booking;

use Illuminate\Http\JsonResponse;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Booking\BookingScheduleResource;
use TitaKita\Services\Application\Handlers\Booking\GetScheduleHandler;

class GetScheduleAction extends BaseAction
{
    public function __construct(
        private readonly GetScheduleHandler $handler,
    ) {}

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $result = $this->handler->handle($eventId);

        return $this->resourceResponse(
            resource: BookingScheduleResource::class,
            data: $result,
        );
    }
}
