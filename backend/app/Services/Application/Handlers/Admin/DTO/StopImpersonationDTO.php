<?php

namespace TitaKita\Services\Application\Handlers\Admin\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class StopImpersonationDTO extends BaseDataObject
{
    public function __construct(
        public readonly int $impersonatorId,
    )
    {
    }
}
