<?php

namespace TitaKita\Http\Actions\SelfService;

use TitaKita\Exceptions\SelfServiceDisabledException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\SelfService\EditAttendeePublicRequest;
use TitaKita\Services\Application\Handlers\SelfService\DTO\EditAttendeePublicDTO;
use TitaKita\Services\Application\Handlers\SelfService\EditAttendeePublicHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class EditAttendeePublicAction extends BaseAction
{
    public function __construct(
        private readonly EditAttendeePublicHandler $handler
    ) {
    }

    public function __invoke(
        EditAttendeePublicRequest $request,
        int $eventId,
        string $orderShortId,
        string $attendeeShortId
    ): JsonResponse {
        try {
            $result = $this->handler->handle(EditAttendeePublicDTO::from([
                'eventId' => $eventId,
                'orderShortId' => $orderShortId,
                'attendeeShortId' => $attendeeShortId,
                'firstName' => $request->input('first_name'),
                'lastName' => $request->input('last_name'),
                'email' => $request->input('email'),
                'ipAddress' => $this->getClientIp($request),
                'userAgent' => $request->userAgent(),
            ]));

            $response = [
                'message' => __('Attendee updated successfully'),
            ];

            if ($result->shortIdChanged && $result->newShortId) {
                $response['new_short_id'] = $result->newShortId;
            }

            return $this->jsonResponse($response);
        } catch (SelfServiceDisabledException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (ResourceNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}
