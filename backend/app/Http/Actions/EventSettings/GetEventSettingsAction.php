<?php

namespace TitaKita\Http\Actions\EventSettings;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\EventSettingsRepositoryInterface;
use TitaKita\Resources\Event\EventSettingsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GetEventSettingsAction extends BaseAction
{
    public function __construct(private readonly EventSettingsRepositoryInterface $eventSettingsRepository)
    {
    }

    public function __invoke(int $eventId): Response|JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $settings = $this->eventSettingsRepository->findFirstWhere([
            'event_id' => $eventId
        ]);

        if ($settings === null) {
            return $this->notFoundResponse();
        }

        return $this->resourceResponse(EventSettingsResource::class, $settings);
    }
}
