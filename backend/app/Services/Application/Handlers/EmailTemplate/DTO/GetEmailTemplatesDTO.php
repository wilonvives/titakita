<?php

namespace TitaKita\Services\Application\Handlers\EmailTemplate\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;
use TitaKita\DomainObjects\Enums\EmailTemplateType;

class GetEmailTemplatesDTO extends BaseDataObject
{
    public function __construct(
        public readonly int                $account_id,
        public readonly ?int               $organizer_id = null,
        public readonly ?int               $event_id = null,
        public readonly ?EmailTemplateType $template_type = null,
        public readonly bool               $include_inactive = false,
    )
    {
    }
}
