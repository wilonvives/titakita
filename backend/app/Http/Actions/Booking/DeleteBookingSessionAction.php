<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Booking;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Booking\BookingScheduleResource;
use TitaKita\Services\Application\Handlers\Booking\DeleteBookingSessionHandler;
use TitaKita\Services\Domain\Booking\Exception\SessionHasBookingsException;
use TitaKita\Services\Domain\Booking\Exception\SessionNotFoundException;

class DeleteBookingSessionAction extends BaseAction
{
    public function __construct(private readonly DeleteBookingSessionHandler $handler) {}

    public function __invoke(int $eventId, int $productId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $result = $this->handler->handle($eventId, $productId);
        } catch (SessionNotFoundException|SessionHasBookingsException $e) {
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
