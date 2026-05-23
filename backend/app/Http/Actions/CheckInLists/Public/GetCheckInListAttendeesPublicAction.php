<?php

namespace TitaKita\Http\Actions\CheckInLists\Public;

use TitaKita\Exceptions\CannotCheckInException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Resources\Attendee\AttendeeWithCheckInPublicResource;
use TitaKita\Services\Application\Handlers\CheckInList\Public\GetCheckInListAttendeesPublicHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GetCheckInListAttendeesPublicAction extends BaseAction
{
    public function __construct(
        private readonly GetCheckInListAttendeesPublicHandler $getCheckInListAttendeesPublicHandler,
    )
    {
    }

    public function __invoke(string $checkInListShortId, Request $request): JsonResponse
    {
        try {
            $attendees = $this->getCheckInListAttendeesPublicHandler->handle(
                shortId: $checkInListShortId,
                queryParams: QueryParamsDTO::fromArray($request->query->all())
            );
        } catch (CannotCheckInException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: Response::HTTP_FORBIDDEN,
            );
        }

        return $this->resourceResponse(
            resource: AttendeeWithCheckInPublicResource::class,
            data: $attendees,
        );
    }
}
