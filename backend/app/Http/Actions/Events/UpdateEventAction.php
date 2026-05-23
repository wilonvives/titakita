<?php

namespace TitaKita\Http\Actions\Events;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exceptions\CannotChangeCurrencyException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Event\UpdateEventRequest;
use TitaKita\Resources\Event\EventResource;
use TitaKita\Services\Application\Handlers\Event\DTO\UpdateEventDTO;
use TitaKita\Services\Application\Handlers\Event\UpdateEventHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class UpdateEventAction extends BaseAction
{
    public function __construct(
        private readonly UpdateEventHandler $updateEventHandler
    )
    {
    }

    /**
     * @throws Throwable|ValidationException
     */
    public function __invoke(UpdateEventRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);
        $authorisedUser = $this->getAuthenticatedUser();

        try {
            $event = $this->updateEventHandler->handle(
                UpdateEventDTO::fromArray(
                    array_merge(
                        $request->validated(),
                        [
                            'id' => $eventId,
                            'account_id' => $this->getAuthenticatedAccountId(),
                            'user_id' => $authorisedUser->getId(),
                        ]
                    )
                )
            );
        } catch (CannotChangeCurrencyException $exception) {
            throw ValidationException::withMessages([
                'currency' => $exception->getMessage(),
            ]);
        }

        return $this->resourceResponse(EventResource::class, $event);
    }
}
