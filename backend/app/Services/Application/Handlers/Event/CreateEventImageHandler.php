<?php

namespace TitaKita\Services\Application\Handlers\Event;

use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\Services\Application\Handlers\Event\DTO\CreateEventImageDTO;
use TitaKita\Services\Domain\Event\CreateEventImageService;
use Throwable;

class CreateEventImageHandler
{
    public function __construct(
        private readonly CreateEventImageService $createEventImageService,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(CreateEventImageDTO $imageData): ImageDomainObject
    {
        return $this->createEventImageService->createImage(
            eventId: $imageData->eventId,
            accountId: $imageData->accountId,
            image: $imageData->image,
            imageType: $imageData->imageType,
        );
    }
}
