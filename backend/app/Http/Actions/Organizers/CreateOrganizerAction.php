<?php

namespace TitaKita\Http\Actions\Organizers;

use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Organizer\UpsertOrganizerRequest;
use TitaKita\Http\ResponseCodes;
use TitaKita\Resources\Organizer\OrganizerResource;
use TitaKita\Services\Application\Handlers\Organizer\CreateOrganizerHandler;
use TitaKita\Services\Application\Handlers\Organizer\DTO\CreateOrganizerDTO;
use Illuminate\Http\JsonResponse;

class CreateOrganizerAction extends BaseAction
{
    public function __construct(private readonly CreateOrganizerHandler $createOrganizerHandler)
    {
    }

    public function __invoke(UpsertOrganizerRequest $request): JsonResponse
    {
        $organizerData = array_merge(
            $request->validated(),
            [
                'account_id' => $this->getAuthenticatedAccountId(),
            ]
        );

        $organizer = $this->createOrganizerHandler->handle(
            organizerData: CreateOrganizerDTO::fromArray($organizerData),
        );

        return $this->resourceResponse(
            resource: OrganizerResource::class,
            data: $organizer,
            statusCode: ResponseCodes::HTTP_CREATED,
        );
    }
}
