<?php

namespace TitaKita\Services\Application\Handlers\Account\Vat\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class UpsertAccountVatSettingDTO extends BaseDataObject
{
    public function __construct(
        public readonly int $accountId,
        public readonly bool $vatRegistered,
        public readonly ?string $vatNumber = null,
    ) {
    }
}
