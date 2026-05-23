<?php

namespace TitaKita\Http\Actions\Events;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exceptions\AccountNotVerifiedException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Event\UpdateEventStatusRequest;
use TitaKita\Http\ResponseCodes;
use TitaKita\Resources\Event\EventResource;
use TitaKita\Services\Application\Handlers\Event\DTO\UpdateEventStatusDTO;
use TitaKita\Services\Application\Handlers\Event\UpdateEventStatusHandler;
use Illuminate\Http\JsonResponse;

class UpdateEventStatusAction extends BaseAction
{
    public function __construct(
        private readonly UpdateEventStatusHandler $updateEventStatusHandler,
    )
    {
    }

    public function __invoke(UpdateEventStatusRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $updatedEvent = $this->updateEventStatusHandler->handle(UpdateEventStatusDTO::fromArray([
                'status' => $request->input('status'),
                'eventId' => $eventId,
                'accountId' => $this->getAuthenticatedAccountId(),
            ]));
        } catch (AccountNotVerifiedException $e) {
            return $this->errorResponse($e->getMessage(), ResponseCodes::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->resourceResponse(EventResource::class, $updatedEvent);
    }
}
