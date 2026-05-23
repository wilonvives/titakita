<?php

namespace TitaKita\Http\Actions\CheckInLists\Public;

use TitaKita\Exceptions\CannotCheckInException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\CheckInList\CreateAttendeeCheckInPublicRequest;
use TitaKita\Resources\CheckInList\AttendeeCheckInPublicResource;
use TitaKita\Services\Application\Handlers\CheckInList\Public\CreateAttendeeCheckInPublicHandler;
use TitaKita\Services\Application\Handlers\CheckInList\Public\DTO\CreateAttendeeCheckInPublicDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CreateAttendeeCheckInPublicAction extends BaseAction
{
    public function __construct(
        private readonly CreateAttendeeCheckInPublicHandler $createAttendeeCheckInPublicHandler,
    )
    {
    }

    public function __invoke(
        string                             $checkInListUuid,
        CreateAttendeeCheckInPublicRequest $request,
    ): JsonResponse
    {
        try {
            $checkIns = $this->createAttendeeCheckInPublicHandler->handle(CreateAttendeeCheckInPublicDTO::from([
                'checkInListUuid' => $checkInListUuid,
                'checkInUserIpAddress' => $request->ip(),
                'attendeesAndActions' => $request->validated('attendees'),
            ]));
        } catch (CannotCheckInException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: Response::HTTP_CONFLICT,
            );
        }

        return $this->resourceResponse(
            resource: AttendeeCheckInPublicResource::class,
            data: $checkIns->attendeeCheckIns,
            errors: $checkIns->errors->toArray()
        );
    }
}
