<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Booking;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Booking\BookingScheduleResource;
use TitaKita\Services\Application\Handlers\Booking\DTO\SaveBookingSettingsDTO;
use TitaKita\Services\Application\Handlers\Booking\SaveBookingSettingsHandler;

class SaveBookingSettingsAction extends BaseAction
{
    public function __construct(private readonly SaveBookingSettingsHandler $handler) {}

    public function __invoke(int $eventId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $request->merge(['event_id' => $eventId]);

        $result = $this->handler->handle(SaveBookingSettingsDTO::from($request->all()));

        return $this->resourceResponse(
            resource: BookingScheduleResource::class,
            data: $result,
        );
    }
}
