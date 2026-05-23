<?php

namespace TitaKita\Services\Application\Handlers\Event\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\DomainObjects\Enums\ImageType;
use Illuminate\Http\UploadedFile;

class CreateEventImageDTO extends BaseDTO
{
    public function __construct(
        public readonly int          $eventId,
        public readonly int          $accountId,
        public readonly UploadedFile $image,
        public readonly ImageType    $imageType,
    )
    {
    }
}
