<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\Models\Image;
use TitaKita\Repository\Interfaces\ImageRepositoryInterface;

/**
 * @extends BaseRepository<ImageDomainObject>
 */
class ImageRepository extends BaseRepository implements ImageRepositoryInterface
{
    protected function getModel(): string
    {
        return Image::class;
    }

    public function getDomainObject(): string
    {
        return ImageDomainObject::class;
    }
}
