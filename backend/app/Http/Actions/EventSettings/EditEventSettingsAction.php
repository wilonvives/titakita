<?php

namespace TitaKita\Http\Actions\EventSettings;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\EventSettings\UpdateEventSettingsRequest;
use TitaKita\Resources\Event\EventSettingsResource;
use TitaKita\Services\Application\Handlers\EventSettings\DTO\UpdateEventSettingsDTO;
use TitaKita\Services\Application\Handlers\EventSettings\UpdateEventSettingsHandler;
use Illuminate\Http\JsonResponse;

class EditEventSettingsAction extends BaseAction
{
    public function __construct(
        private readonly UpdateEventSettingsHandler $updateEventSettingsHandler
    )
    {
    }

    public function __invoke(UpdateEventSettingsRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $settings = array_merge(
            $request->validated(),
            [
                'event_id' => $eventId,
                'account_id' => $this->getAuthenticatedAccountId(),
            ],
        );

        $event = $this->updateEventSettingsHandler->handle(
            UpdateEventSettingsDTO::fromArray($settings),
        );

        return $this->resourceResponse(EventSettingsResource::class, $event);
    }
}
