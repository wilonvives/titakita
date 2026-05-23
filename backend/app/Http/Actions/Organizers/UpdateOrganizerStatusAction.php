<?php

namespace TitaKita\Http\Actions\Organizers;

use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Exceptions\AccountNotVerifiedException;
use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Organizer\UpdateOrganizerStatusRequest;
use TitaKita\Http\ResponseCodes;
use TitaKita\Resources\Organizer\OrganizerResource;
use TitaKita\Services\Application\Handlers\Organizer\DTO\UpdateOrganizerStatusDTO;
use TitaKita\Services\Application\Handlers\Organizer\UpdateOrganizerStatusHandler;
use Illuminate\Http\JsonResponse;

class UpdateOrganizerStatusAction extends BaseAction
{
    public function __construct(
        private readonly UpdateOrganizerStatusHandler $updateOrganizerStatusHandler,
    )
    {
    }

    public function __invoke(UpdateOrganizerStatusRequest $request, int $organizerId): JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        try {
            $updatedOrganizer = $this->updateOrganizerStatusHandler->handle(UpdateOrganizerStatusDTO::fromArray([
                'status' => $request->input('status'),
                'organizerId' => $organizerId,
                'accountId' => $this->getAuthenticatedAccountId(),
            ]));
        } catch (AccountNotVerifiedException $e) {
            return $this->errorResponse($e->getMessage(), ResponseCodes::HTTP_UNPROCESSABLE_ENTITY);
        } catch (CannotDeleteEntityException $e) {
            return $this->errorResponse($e->getMessage(), ResponseCodes::HTTP_CONFLICT);
        }

        return $this->resourceResponse(OrganizerResource::class, $updatedOrganizer);
    }
}
