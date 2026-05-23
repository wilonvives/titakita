<?php

namespace TitaKita\Http\Actions\Events\Images;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\ImageRepositoryInterface;
use TitaKita\Resources\Image\ImageResource;
use Illuminate\Http\JsonResponse;

class GetEventImagesAction extends BaseAction
{
    public function __construct(private readonly ImageRepositoryInterface $imageRepository)
    {
    }

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $images = $this->imageRepository->findWhere([
            'entity_id' => $eventId,
            'entity_type' => EventDomainObject::class,
        ]);

        return $this->resourceResponse(ImageResource::class, $images);
    }
}
