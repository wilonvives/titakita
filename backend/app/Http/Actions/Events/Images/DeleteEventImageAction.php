<?php

namespace TitaKita\Http\Actions\Events\Images;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Event\DeleteEventImageHandler;
use TitaKita\Services\Application\Handlers\Event\DTO\DeleteEventImageDTO;
use Illuminate\Http\Response;

class DeleteEventImageAction extends BaseAction
{
    public function __construct(private readonly DeleteEventImageHandler $deleteEventImageHandler)
    {
    }

    public function __invoke(int $eventId, int $imageId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $this->deleteEventImageHandler->handle(new DeleteEventImageDTO(
            eventId: $eventId,
            imageId: $imageId,
        ));

        return $this->deletedResponse();
    }
}
