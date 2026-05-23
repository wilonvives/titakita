<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Account\Vat;

use TitaKita\DomainObjects\AccountVatSettingDomainObject;
use TitaKita\Repository\Interfaces\AccountVatSettingRepositoryInterface;

class GetAccountVatSettingHandler
{
    public function __construct(
        private readonly AccountVatSettingRepositoryInterface $vatSettingRepository,
    ) {
    }

    public function handle(int $accountId): ?AccountVatSettingDomainObject
    {
        return $this->vatSettingRepository->findByAccountId($accountId);
    }
}
