<?php

namespace TitaKita\Http\Actions\EventSettings;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\EventSettings\UpdateEventSettingsRequest;
use TitaKita\Resources\Event\EventSettingsResource;
use TitaKita\Services\Application\Handlers\EventSettings\DTO\PartialUpdateEventSettingsDTO;
use TitaKita\Services\Application\Handlers\EventSettings\PartialUpdateEventSettingsHandler;
use Illuminate\Http\JsonResponse;
use Throwable;

class PartialEditEventSettingsAction extends BaseAction
{
    public function __construct(
        private readonly PartialUpdateEventSettingsHandler $partialUpdateEventSettingsHandler
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(UpdateEventSettingsRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $event = $this->partialUpdateEventSettingsHandler->handle(
            PartialUpdateEventSettingsDTO::fromArray([
                'settings' => $request->validated(),
                'event_id' => $eventId,
                'account_id' => $this->getAuthenticatedAccountId(),
            ]),
        );

        return $this->resourceResponse(EventSettingsResource::class, $event);
    }
}
