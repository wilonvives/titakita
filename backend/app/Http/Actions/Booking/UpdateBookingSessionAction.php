<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Booking;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Booking\BookingScheduleResource;
use TitaKita\Services\Application\Handlers\Booking\DTO\UpdateBookingSessionDTO;
use TitaKita\Services\Application\Handlers\Booking\UpdateBookingSessionHandler;
use TitaKita\Services\Domain\Booking\Exception\SessionNotFoundException;

class UpdateBookingSessionAction extends BaseAction
{
    public function __construct(private readonly UpdateBookingSessionHandler $handler) {}

    public function __invoke(int $eventId, int $productId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $request->merge([
            'event_id' => $eventId,
            'product_id' => $productId,
        ]);

        try {
            $result = $this->handler->handle(UpdateBookingSessionDTO::from($request->all()));
        } catch (SessionNotFoundException $e) {
            throw ValidationException::withMessages([
                'session' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(
            resource: BookingScheduleResource::class,
            data: $result,
        );
    }
}
