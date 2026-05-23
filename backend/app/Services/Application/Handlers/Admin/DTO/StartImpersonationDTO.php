<?php

namespace TitaKita\Services\Application\Handlers\Admin\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class StartImpersonationDTO extends BaseDataObject
{
    public function __construct(
        public readonly int $userId,
        public readonly int $accountId,
        public readonly int $impersonatorId,
    )
    {
    }
}
