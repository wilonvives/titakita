<?php

namespace TitaKita\Services\Domain\Event;

use TitaKita\DomainObjects\Enums\ImageType;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\Repository\Interfaces\ImageRepositoryInterface;
use TitaKita\Services\Domain\Image\ImageUploadService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\UploadedFile;
use Throwable;

/**
 * @deprecated use CreateImageAction
 */
class CreateEventImageService
{
    public function __construct(
        private readonly ImageUploadService       $imageUploadService,
        private readonly ImageRepositoryInterface $imageRepository,
        private readonly DatabaseManager          $databaseManager,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function createImage(
        int          $eventId,
        int          $accountId,
        UploadedFile $image,
        ImageType    $imageType,
    ): ImageDomainObject
    {
        return $this->databaseManager->transaction(function () use ($accountId, $image, $eventId, $imageType) {
            if ($imageType === ImageType::EVENT_COVER) {
                $this->imageRepository->deleteWhere([
                    'entity_id' => $eventId,
                    'entity_type' => EventDomainObject::class,
                    'type' => ImageType::EVENT_COVER->name,
                ]);
            }

            return $this->imageUploadService->upload(
                image: $image,
                entityId: $eventId,
                entityType: EventDomainObject::class,
                imageType: $imageType->name,
                accountId: $accountId,
            );
        });
    }
}
