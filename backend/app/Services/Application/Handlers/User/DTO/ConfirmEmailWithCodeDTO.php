<?php

namespace TitaKita\Services\Application\Handlers\User\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class ConfirmEmailWithCodeDTO extends BaseDataObject
{
    public string $code;
    public int $userId;
    public int $accountId;
}
