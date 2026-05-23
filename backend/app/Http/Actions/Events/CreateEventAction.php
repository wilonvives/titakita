<?php

namespace TitaKita\Http\Actions\Events;

use TitaKita\Exceptions\OrganizerNotFoundException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Event\CreateEventRequest;
use TitaKita\Resources\Event\EventResource;
use TitaKita\Services\Application\Handlers\Event\CreateEventHandler;
use TitaKita\Services\Application\Handlers\Event\DTO\CreateEventDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateEventAction extends BaseAction
{
    public function __construct(
        private readonly CreateEventHandler $createEventHandler
    )
    {
    }

    /**
     * @throws ValidationException|Throwable
     */
    public function __invoke(CreateEventRequest $request): JsonResponse
    {
        $authorisedUser = $this->getAuthenticatedUser();

        $eventData = array_merge(
            $request->validated(),
            [
                'account_id' => $this->getAuthenticatedAccountId(),
                'user_id' => $authorisedUser->getId(),
            ]
        );

        try {
            $event = $this->createEventHandler->handle(
                eventData: CreateEventDTO::fromArray($eventData)
            );
        } catch (OrganizerNotFoundException $e) {
            throw ValidationException::withMessages([
                'organizer_id' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(EventResource::class, $event);
    }
}
