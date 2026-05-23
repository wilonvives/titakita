<?php

namespace TitaKita\Resources\Image;

use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\Helper\Url;
use TitaKita\Resources\BaseResource;

/**
 * @mixin ImageDomainObject
 */
class ImageResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getId(),
            'url' => Url::getCdnUrl($this->getPath()),
            'path' => $this->getPath(),
            'size' => $this->getSize(),
            'file_name' => $this->getFileName(),
            'mime_type' => $this->getMimeType(),
            'type' => $this->getType(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'avg_colour' => $this->getAvgColour(),
            'lqip_base64' => $this->getLqipBase64(),
        ];
    }
}
