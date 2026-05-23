<?php

namespace TitaKita\Services\Application\Handlers\EmailTemplate\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;
use TitaKita\DomainObjects\Enums\EmailTemplateType;

class PreviewEmailTemplateDTO extends BaseDataObject
{
    public function __construct(
        public readonly string            $subject,
        public readonly string            $body,
        public readonly EmailTemplateType $template_type,
        public readonly ?array            $cta = null,
    )
    {
    }
}
