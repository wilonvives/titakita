<?php

namespace TitaKita\Services\Domain\Email\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class RenderedEmailTemplateDTO extends BaseDataObject
{
    public function __construct(
        public readonly string $subject,
        public readonly string $body,
        public readonly ?array $cta = null,
    )
    {
    }
}