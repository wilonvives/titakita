<?php

namespace TitaKita\Http\Actions\SelfService;

use TitaKita\Exceptions\SelfServiceDisabledException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\SelfService\DTO\ResendEmailPublicDTO;
use TitaKita\Services\Application\Handlers\SelfService\ResendAttendeeTicketPublicHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ResendAttendeeTicketPublicAction extends BaseAction
{
    public function __construct(
        private readonly ResendAttendeeTicketPublicHandler $handler
    ) {
    }

    public function __invoke(
        Request $request,
        int $eventId,
        string $orderShortId,
        string $attendeeShortId
    ): JsonResponse {
        try {
            $this->handler->handle(ResendEmailPublicDTO::from([
                'eventId' => $eventId,
                'orderShortId' => $orderShortId,
                'attendeeShortId' => $attendeeShortId,
                'ipAddress' => $this->getClientIp($request),
                'userAgent' => $request->userAgent(),
            ]));

            return $this->jsonResponse([
                'message' => __('Ticket resent successfully'),
            ]);
        } catch (SelfServiceDisabledException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (ResourceNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}
