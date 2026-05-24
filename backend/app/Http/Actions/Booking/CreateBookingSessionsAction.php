<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Booking;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\ResponseCodes;
use TitaKita\Resources\Booking\BookingScheduleResource;
use TitaKita\Services\Application\Handlers\Booking\CreateBookingSessionsHandler;
use TitaKita\Services\Application\Handlers\Booking\DTO\CreateBookingSessionsDTO;

class CreateBookingSessionsAction extends BaseAction
{
    public function __construct(private readonly CreateBookingSessionsHandler $handler) {}

    /**
     * @throws Throwable
     */
    public function __invoke(int $eventId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $request->merge(['event_id' => $eventId]);

        $result = $this->handler->handle(CreateBookingSessionsDTO::from($request->all()));

        return $this->resourceResponse(
            resource: BookingScheduleResource::class,
            data: $result,
            statusCode: ResponseCodes::HTTP_CREATED,
        );
    }
}
