<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Booking;

use Illuminate\Http\JsonResponse;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Booking\GetBookingSessionsHandler;

class GetPublicBookingSessionsAction extends BaseAction
{
    public function __construct(private readonly GetBookingSessionsHandler $handler) {}

    public function __invoke(int $eventId): JsonResponse
    {
        $sessions = $this->handler->handle($eventId);

        return $this->jsonResponse($sessions->all(), wrapInData: true);
    }
}
